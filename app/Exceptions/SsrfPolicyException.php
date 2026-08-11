<?php

namespace App\Exceptions;

use RuntimeException;

class SsrfPolicyException extends RuntimeException
{
    private string $reason;
    private bool $retryable;

    private function __construct(string $message, string $reason, bool $retryable = false)
    {
        parent::__construct($message);
        $this->reason = $reason;
        $this->retryable = $retryable;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public static function invalidUrl(string $url): self
    {
        return new self(
            "Invalid URL format",
            'invalid_url'
        );
    }

    public static function schemeRejected(string $url, string $scheme): self
    {
        return new self(
            "Only HTTPS URLs are permitted for calendar links",
            'rejected_scheme'
        );
    }

    public static function embeddedCredentials(string $url): self
    {
        return new self(
            "URLs with embedded credentials are not permitted",
            'embedded_credentials'
        );
    }

    public static function privateAddress(string $url): self
    {
        return new self(
            "The calendar URL resolves to a non-public address and cannot be fetched",
            'rejected_address'
        );
    }

    public static function dnsResolutionFailed(string $url, string $host): self
    {
        return new self(
            "Unable to resolve the hostname for the calendar URL",
            'dns_failed',
            true
        );
    }

    public static function timeout(string $url, string $detail): self
    {
        return new self(
            "The calendar URL did not respond within the allowed time",
            'timeout',
            true
        );
    }

    public static function oversize(string $url, int $maxBytes): self
    {
        return new self(
            "The calendar feed exceeds the maximum allowed size",
            'oversize'
        );
    }

    public static function tooManyRedirects(string $url): self
    {
        return new self(
            "The calendar URL exceeded the maximum number of redirects",
            'too_many_redirects'
        );
    }

    public static function fetchFailed(string $url, string $detail): self
    {
        return new self(
            "Failed to retrieve the calendar feed",
            'fetch_failed',
            true
        );
    }

    public function getOwnerFacingMessage(): string
    {
        return $this->getMessage();
    }
}
