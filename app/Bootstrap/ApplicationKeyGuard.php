<?php

namespace App\Bootstrap;

use RuntimeException;

class ApplicationKeyGuard
{
    const PLACEHOLDER_KEY = '<generated-app-key>';

    /**
     * Environments that may boot without a production-ready application key.
     *
     * @var array
     */
    protected $permissiveEnvironments = ['local', 'testing'];

    /**
     * Ensure a non-local environment has a real application key.
     *
     * @param  string  $environment
     * @param  string|null  $key
     * @return void
     *
     * @throws \RuntimeException
     */
    public function assertSecureKey($environment, $key)
    {
        if (in_array($environment, $this->permissiveEnvironments, true)) {
            return;
        }

        if ($this->isMissingOrPlaceholder($key)) {
            throw new RuntimeException(
                'Application key is missing or still a placeholder. '.
                'Set APP_KEY to a generated value (run `php artisan key:generate`) '.
                'before starting the application outside the local/testing environment.'
            );
        }
    }

    /**
     * @param  string|null  $key
     * @return bool
     */
    public function isMissingOrPlaceholder($key)
    {
        if ($key === null) {
            return true;
        }

        $normalized = trim((string) $key);

        if ($normalized === '') {
            return true;
        }

        if ($normalized === self::PLACEHOLDER_KEY) {
            return true;
        }

        return false;
    }
}
