<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Calendar sharing screen test fixtures.
 * Provides a business with an active token, a revoked token,
 * and several audit denial rows for deterministic rendering.
 */
class CalendarSharingFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $businessId = 1;

        DB::table('ical_tokens')->insert([
            [
                'business_id' => $businessId,
                'token_hash'  => hash('sha256', 'revoked-legacy-token'),
                'rotated_at'  => '2026-06-15 08:00:00',
                'revoked_at'  => '2026-07-01 10:00:00',
                'last_used_at' => '2026-06-30 22:00:00',
                'created_at'  => '2026-05-01 09:00:00',
                'updated_at'  => '2026-07-01 10:00:00',
            ],
            [
                'business_id' => $businessId,
                'token_hash'  => hash('sha256', 'active-fixture-token'),
                'rotated_at'  => '2026-07-01 10:00:00',
                'revoked_at'  => null,
                'last_used_at' => $now->copy()->subHours(2)->toDateTimeString(),
                'created_at'  => '2026-07-01 10:00:00',
                'updated_at'  => $now->copy()->subHours(2)->toDateTimeString(),
            ],
        ]);

        DB::table('audit_logs')->insert([
            [
                'actor_id'       => null,
                'entity_type'    => 'ical_feed',
                'entity_id'      => (string) $businessId,
                'action'         => 'access_denied',
                'correlation_id' => 'corr-deny-001',
                'context'        => json_encode(['reason' => 'revoked_token', 'outcome' => 'denied']),
                'created_at'     => $now->copy()->subDays(3),
            ],
            [
                'actor_id'       => null,
                'entity_type'    => 'ical_feed',
                'entity_id'      => (string) $businessId,
                'action'         => 'access_denied',
                'correlation_id' => 'corr-deny-002',
                'context'        => json_encode(['reason' => 'invalid_token', 'outcome' => 'denied']),
                'created_at'     => $now->copy()->subDays(1),
            ],
            [
                'actor_id'       => null,
                'entity_type'    => 'ical_feed',
                'entity_id'      => (string) $businessId,
                'action'         => 'access_denied',
                'correlation_id' => 'corr-deny-003',
                'context'        => json_encode(['reason' => 'expired_token', 'outcome' => 'denied']),
                'created_at'     => $now->copy()->subHours(6),
            ],
            [
                'actor_id'       => null,
                'entity_type'    => 'ical_feed',
                'entity_id'      => (string) $businessId,
                'action'         => 'guard_divergence',
                'correlation_id' => 'corr-div-001',
                'context'        => json_encode(['legacy_result' => 'allow', 'guard_result' => 'deny']),
                'created_at'     => $now->copy()->subDays(5),
            ],
        ]);
    }
}
