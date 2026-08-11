<?php

namespace App\Http\Middleware;

use App\Logging\CorrelationContext;
use App\TG\AuditLogger;
use App\TG\Business\Token as BusinessToken;
use App\TG\ICalTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;

class ICalTokenGuard
{
    public function __construct(
        private readonly ICalTokenService $tokenService,
        private readonly AuditLogger $audit,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $businessSlug = $request->route('business');
        $token = $request->route('token');

        $business = $this->resolveBusiness($businessSlug);

        if ($business === null) {
            $this->audit->denied('ical.access', 'business', null, [
                'reason' => 'unknown_business',
                'slug'   => is_string($businessSlug) ? substr($businessSlug, 0, 64) : null,
            ]);

            return $this->errorResponse(404, 'not_found', 'Resource not found.');
        }

        if (!$this->isWellFormed($token)) {
            $this->audit->denied('ical.access', 'business', $business->id, [
                'reason' => 'malformed_token',
            ]);

            return $this->errorResponse(400, 'bad_request', 'Malformed token.');
        }

        $mode = config('ical.guard_mode', 'shadow');

        if ($mode === 'enforced') {
            return $this->evaluateEnforced($request, $next, $business, $token);
        }

        return $this->evaluateShadow($request, $next, $business, $token);
    }

    private function evaluateEnforced(Request $request, Closure $next, Business $business, string $token)
    {
        $guardResult = $this->tokenService->validate($business, $token);

        if (!$guardResult) {
            $this->audit->denied('ical.access', 'business', $business->id, [
                'reason' => 'invalid_token',
            ]);

            return $this->errorResponse(403, 'forbidden', 'Access denied.');
        }

        $this->audit->append(
            action: 'ical.access',
            resourceType: 'business',
            resourceId: $business->id,
            outcome: 'success',
        );

        $request->attributes->set('ical_business', $business);

        return $next($request);
    }

    private function evaluateShadow(Request $request, Closure $next, Business $business, string $token)
    {
        $guardResult = $this->tokenService->validate($business, $token);

        $legacyToken = (new BusinessToken($business))->generate();
        $legacyResult = hash_equals($legacyToken, $token);

        if ($guardResult !== $legacyResult) {
            Log::channel('security')->warning('ical.token.divergence', [
                'business_id'    => $business->id,
                'guard_result'   => $guardResult,
                'legacy_result'  => $legacyResult,
                'correlation_id' => CorrelationContext::id(),
            ]);
        }

        if (!$legacyResult) {
            $this->audit->denied('ical.access', 'business', $business->id, [
                'reason' => 'invalid_token',
                'mode'   => 'shadow',
            ]);

            return $this->errorResponse(403, 'forbidden', 'Access denied.');
        }

        $this->audit->append(
            action: 'ical.access',
            resourceType: 'business',
            resourceId: $business->id,
            outcome: 'success',
        );

        $request->attributes->set('ical_business', $business);

        return $next($request);
    }

    private function resolveBusiness($slug): ?Business
    {
        if (!is_string($slug) || $slug === '') {
            return null;
        }

        return Business::where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();
    }

    private function isWellFormed(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        if (strlen($token) > 128) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z0-9]+$/', $token);
    }

    private function errorResponse(int $status, string $code, string $message)
    {
        $body = [
            'error' => [
                'code'           => $code,
                'message'        => $message,
                'correlation_id' => CorrelationContext::id(),
            ],
        ];

        return response()->json($body, $status);
    }
}
