<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class PiiRedactionProcessor implements ProcessorInterface
{
    private const REDACTED = '[REDACTED]';

    private const MAX_DEPTH = 10;

    private const DENIED_KEYS = [
        'email',
        'mobile',
        'nin',
        'password',
        'password_confirmation',
        'provider_user',
        'access_token',
        'refresh_token',
        'birthdate',
        'postal_address',
        'contact_email',
        'last_ip',
        'secret',
        'token',
        'credit_card',
        'ssn',
        'mobile_country',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            $context = $this->redactRecursive($record->context, 0);
            $extra = $this->redactRecursive($record->extra, 0);
            $message = $this->redactMessage($record->message);

            return $record->with(context: $context, extra: $extra, message: $message);
        } catch (\Throwable) {
            return $record->with(
                context: ['_redaction_error' => 'Context dropped due to processor failure'],
                extra: $record->extra,
            );
        }
    }

    private function redactRecursive(mixed $data, int $depth): mixed
    {
        if ($depth >= self::MAX_DEPTH) {
            return self::REDACTED;
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                if (is_string($key) && $this->isDeniedKey($key)) {
                    $result[$key] = self::REDACTED;
                } else {
                    $result[$key] = $this->redactRecursive($value, $depth + 1);
                }
            }
            return $result;
        }

        if (is_object($data)) {
            $result = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                if ($this->isDeniedKey($key)) {
                    $result->{$key} = self::REDACTED;
                } else {
                    $result->{$key} = $this->redactRecursive($value, $depth + 1);
                }
            }
            return $result;
        }

        return $data;
    }

    private function isDeniedKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::DENIED_KEYS as $denied) {
            if ($normalized === $denied) {
                return true;
            }
        }

        return false;
    }

    private function redactMessage(string $message): string
    {
        $message = preg_replace(
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
            '[EMAIL_REDACTED]',
            $message
        );

        $message = preg_replace(
            '/\b(\+?\d{1,3}[\s\-]?)?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{4}\b/',
            '[PHONE_REDACTED]',
            $message
        );

        return $message;
    }
}
