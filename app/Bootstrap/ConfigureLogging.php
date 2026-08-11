<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

/**
 * @deprecated Retained for backward-compatibility during the 5.6 hop.
 *             Logging is now configured via config/logging.php (channels).
 *             This class will be removed in WO-023 (logging channel rewrite).
 */
class ConfigureLogging
{
    public function bootstrap(Application $app): void
    {
        // No-op: logging is now channel-based via config/logging.php.
        // The syslog channel preserves the original behaviour.
    }
}
