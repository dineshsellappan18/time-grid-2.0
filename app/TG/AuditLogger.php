<?php

namespace App\TG;

use App\Logging\CorrelationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    private const TABLE = 'audit_logs';

    private const RESTRICTED_KEYS = [
        'nin', 'mobile', 'birthdate', 'password', 'password_confirmation',
        'access_token', 'refresh_token', 'provider_user', 'email',
        'postal_address', 'mobile_country', 'last_ip',
    ];

    public function append(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        string $outcome = 'success',
        ?array $changes = null,
        ?string $actorType = null,
        ?int $actorId = null,
    ): void {
        $actorType = $actorType ?? $this->resolveActorType();
        $actorId = $actorId ?? $this->resolveActorId();

        $sanitizedChanges = $changes !== null ? $this->sanitizeChanges($changes) : null;

        $record = [
            'actor_type'     => $actorType,
            'actor_id'       => $actorId,
            'action'         => $action,
            'resource_type'  => $resourceType,
            'resource_id'    => $resourceId,
            'outcome'        => $outcome,
            'changes'        => $sanitizedChanges !== null ? json_encode($sanitizedChanges) : null,
            'correlation_id' => CorrelationContext::id(),
            'ip_hash'        => $this->hashIp(),
            'occurred_at'    => now(),
        ];

        try {
            DB::table(self::TABLE)->insert($record);
        } catch (\Throwable $e) {
            Log::channel('security')->error('audit.write_failed', [
                'action'        => $action,
                'resource_type' => $resourceType,
                'resource_id'   => $resourceId,
                'error'         => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function denied(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $context = null,
    ): void {
        $this->append(
            action: $action,
            resourceType: $resourceType,
            resourceId: $resourceId,
            outcome: 'denied',
            changes: $context,
        );
    }

    private function resolveActorType(): string
    {
        if (app()->runningInConsole()) {
            return 'system';
        }

        $user = auth()->user();

        if ($user === null) {
            return 'anonymous';
        }

        return 'user';
    }

    private function resolveActorId(): ?int
    {
        return auth()->id();
    }

    private function hashIp(): ?string
    {
        $ip = request()->ip();

        if ($ip === null) {
            return null;
        }

        $salt = config('app.key', 'timegrid-audit-salt');

        return hash('sha256', $salt . $ip);
    }

    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        foreach ($changes as $key => $value) {
            if (is_string($key) && $this->isRestricted($key)) {
                $sanitized[$key] = '[CHANGED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeChanges($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function isRestricted(string $key): bool
    {
        return in_array(strtolower($key), self::RESTRICTED_KEYS, true);
    }
}
