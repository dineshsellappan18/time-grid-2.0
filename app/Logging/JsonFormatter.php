<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

class JsonFormatter
{
    public function __invoke(Logger $logger): void
    {
        $correlationProcessor = new CorrelationIdProcessor();

        foreach ($logger->getHandlers() as $handler) {
            $formatter = new MonologJsonFormatter();
            $formatter->includeStacktraces(true);

            $handler->setFormatter($formatter);
            $handler->pushProcessor($correlationProcessor);
        }
    }
}
