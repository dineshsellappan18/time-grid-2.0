<?php

namespace App\Http\Middleware;

use App\Logging\CorrelationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorrelationId
{
    private const HEADER = 'X-Correlation-ID';

    public function handle(Request $request, Closure $next): Response
    {
        $inbound = $request->header(self::HEADER);

        if ($inbound !== null && $inbound !== '') {
            CorrelationContext::set($inbound);
        }

        $correlationId = CorrelationContext::id();

        $request->attributes->set('correlation_id', $correlationId);

        $response = $next($request);

        $response->headers->set(self::HEADER, $correlationId);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        CorrelationContext::reset();
    }
}
