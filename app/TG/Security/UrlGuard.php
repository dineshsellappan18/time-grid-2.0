<?php

namespace App\TG\Security;

use App\Exceptions\SsrfPolicyException;
use Illuminate\Support\Facades\Log;

class UrlGuard
{
    private const MAX_REDIRECT_DEPTH = 5;
    private const CONNECT_TIMEOUT = 5;
    private const READ_TIMEOUT = 10;
    private const MAX_RESPONSE_BYTES = 2 * 1024 * 1024; // 2 MB

    private const PRIVATE_RANGES_V4 = [
        ['10.0.0.0', '10.255.255.255'],
        ['172.16.0.0', '172.31.255.255'],
        ['192.168.0.0', '192.168.255.255'],
        ['127.0.0.0', '127.255.255.255'],
        ['169.254.0.0', '169.254.255.255'],       // link-local
        ['169.254.169.254', '169.254.169.254'],   // cloud metadata
        ['0.0.0.0', '0.255.255.255'],
    ];

    private const PRIVATE_RANGES_V6 = [
        '::1',        // loopback
        'fe80::/10',  // link-local
        'fc00::/7',   // unique-local
    ];

    public function validateUrl(string $url): void
    {
        $this->assertScheme($url);
        $this->assertNoCredentials($url);
        $this->assertHostResolvesToPublic($url);
    }

    public function fetch(string $url): string
    {
        $this->validateUrl($url);

        $ch = curl_init();
        $body = '';
        $bytesReceived = 0;
        $redirectCount = 0;

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::READ_TIMEOUT,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS      => 0,
            CURLOPT_HEADER         => true,
            CURLOPT_NOBODY         => false,
            CURLOPT_USERAGENT      => 'Timegrid-ICalSync/1.0',
        ]);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$body, &$bytesReceived) {
            $bytesReceived += strlen($data);
            if ($bytesReceived > self::MAX_RESPONSE_BYTES) {
                return 0; // abort transfer
            }
            $body .= $data;
            return strlen($data);
        });

        while (true) {
            $body = '';
            $bytesReceived = 0;

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            if ($errno === CURLE_WRITE_ERROR && $bytesReceived > self::MAX_RESPONSE_BYTES) {
                curl_close($ch);
                throw SsrfPolicyException::oversize($url, self::MAX_RESPONSE_BYTES);
            }

            if ($errno === CURLE_OPERATION_TIMEDOUT || $errno === CURLE_COULDNT_CONNECT) {
                curl_close($ch);
                throw SsrfPolicyException::timeout($url, $error);
            }

            if ($errno !== 0) {
                curl_close($ch);
                throw SsrfPolicyException::fetchFailed($url, $error);
            }

            if ($httpCode >= 300 && $httpCode < 400) {
                $redirectCount++;
                if ($redirectCount > self::MAX_REDIRECT_DEPTH) {
                    curl_close($ch);
                    throw SsrfPolicyException::tooManyRedirects($url);
                }

                $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
                if (empty($redirectUrl)) {
                    $redirectUrl = $this->extractLocationHeader($body);
                }

                if (empty($redirectUrl)) {
                    curl_close($ch);
                    throw SsrfPolicyException::fetchFailed($url, 'Redirect with no Location header');
                }

                $this->validateUrl($redirectUrl);
                curl_setopt($ch, CURLOPT_URL, $redirectUrl);
                continue;
            }

            break;
        }

        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw SsrfPolicyException::fetchFailed($url, "HTTP {$httpCode}");
        }

        $headerSize = strpos($body, "\r\n\r\n");
        if ($headerSize !== false) {
            $body = substr($body, $headerSize + 4);
        }

        return $body;
    }

    private function assertScheme(string $url): void
    {
        $parsed = parse_url($url);

        if ($parsed === false || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw SsrfPolicyException::invalidUrl($url);
        }

        if (strtolower($parsed['scheme']) !== 'https') {
            throw SsrfPolicyException::schemeRejected($url, $parsed['scheme']);
        }
    }

    private function assertNoCredentials(string $url): void
    {
        $parsed = parse_url($url);

        if (!empty($parsed['user']) || !empty($parsed['pass'])) {
            throw SsrfPolicyException::embeddedCredentials($url);
        }
    }

    private function assertHostResolvesToPublic(string $url): void
    {
        $parsed = parse_url($url);
        $host = $parsed['host'];

        $records = gethostbynamel($host);
        if ($records === false) {
            $records = [];
            $ipv6Records = dns_get_record($host, DNS_AAAA);
            if ($ipv6Records) {
                foreach ($ipv6Records as $record) {
                    $records[] = $record['ipv6'];
                }
            }
        }

        if (empty($records)) {
            throw SsrfPolicyException::dnsResolutionFailed($url, $host);
        }

        foreach ($records as $ip) {
            if ($this->isPrivateAddress($ip)) {
                throw SsrfPolicyException::privateAddress($url);
            }
        }
    }

    private function isPrivateAddress(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->isPrivateIpv4($ip);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->isPrivateIpv6($ip);
        }

        return true; // fail-closed: unrecognized format is treated as private
    }

    private function isPrivateIpv4(string $ip): bool
    {
        $ipLong = ip2long($ip);

        foreach (self::PRIVATE_RANGES_V4 as [$start, $end]) {
            $startLong = ip2long($start);
            $endLong = ip2long($end);
            if ($ipLong >= $startLong && $ipLong <= $endLong) {
                return true;
            }
        }

        return false;
    }

    private function isPrivateIpv6(string $ip): bool
    {
        $expanded = $this->expandIpv6($ip);

        // ::1 loopback
        if ($expanded === '00000000000000000000000000000001') {
            return true;
        }

        // fe80::/10 link-local
        $prefix10 = substr($expanded, 0, 3);
        if (hexdec($prefix10) >= hexdec('fe8') && hexdec($prefix10) <= hexdec('feb')) {
            return true;
        }

        // fc00::/7 unique-local
        $firstByte = hexdec(substr($expanded, 0, 2));
        if ($firstByte >= 0xfc && $firstByte <= 0xfd) {
            return true;
        }

        return false;
    }

    private function expandIpv6(string $ip): string
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return '';
        }

        return bin2hex($packed);
    }

    private function extractLocationHeader(string $rawResponse): ?string
    {
        if (preg_match('/^Location:\s*(.+)$/mi', $rawResponse, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
