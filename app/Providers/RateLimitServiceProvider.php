<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->defineLoginLimiter();
        $this->definePasswordResetLimiter();
        $this->defineFeedLimiter();
        $this->defineAvailabilityLimiter();
    }

    private function defineLoginLimiter(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower($request->input('email', '')) . '|' . $this->hashedIp($request);

            return Limit::perMinute(10)->by($key)->response(function ($request, $headers) {
                return $this->rateLimitResponse($headers);
            });
        });
    }

    private function definePasswordResetLimiter(): void
    {
        RateLimiter::for('password-reset', function (Request $request) {
            $key = strtolower($request->input('email', '')) . '|' . $this->hashedIp($request);

            return Limit::perMinute(5)->by($key)->response(function ($request, $headers) {
                return $this->rateLimitResponse($headers);
            });
        });
    }

    private function defineFeedLimiter(): void
    {
        RateLimiter::for('ical-feed', function (Request $request) {
            $token = $request->route('token') ?? 'unknown';
            $key = 'feed|' . hash('sha256', $token);

            return Limit::perMinute(60)->by($key)->response(function ($request, $headers) {
                return $this->rateLimitResponse($headers);
            });
        });
    }

    private function defineAvailabilityLimiter(): void
    {
        RateLimiter::for('availability', function (Request $request) {
            $sessionId = $request->session()?->getId();
            $key = $sessionId ?: ('ip|' . $this->hashedIp($request));

            return Limit::perMinute(120)->by($key)->response(function ($request, $headers) {
                return $this->rateLimitResponse($headers);
            });
        });
    }

    private function hashedIp(Request $request): string
    {
        $ip = $request->ip() ?? '0.0.0.0';

        return hash('sha256', config('app.key', 'salt') . $ip);
    }

    private function rateLimitResponse(array $headers)
    {
        $retryAfter = $headers['Retry-After'] ?? 60;

        Log::channel('security')->warning('rate_limit.exceeded', [
            'correlation_id' => \App\Logging\CorrelationContext::id(),
        ]);

        return response()->json([
            'error' => [
                'code'           => 'rate_limited',
                'message'        => 'Too many requests. Please try again later.',
                'correlation_id' => \App\Logging\CorrelationContext::id(),
            ],
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
        ]);
    }
}
