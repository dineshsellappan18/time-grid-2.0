<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class CorrelationIdProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $correlationId = CorrelationContext::id();

        $extra = $record->extra;
        $extra['correlation_id'] = $correlationId;

        return $record->with(extra: $extra);
    }
}
