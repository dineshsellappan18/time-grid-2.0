<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Platform console test fixtures.
 * Seeds audit_logs with varied action types, outcomes, and actors
 * so the console renders deterministically.
 */
class PlatformConsoleFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $entries = [];

        $actions = [
            ['action' => 'rotate_token', 'entity_type' => 'ical_feed', 'outcome' => 'allowed'],
            ['action' => 'access_denied', 'entity_type' => 'ical_feed', 'outcome' => 'denied'],
            ['action' => 'view_sharing_screen', 'entity_type' => 'ical_feed', 'outcome' => 'allowed'],
            ['action' => 'login', 'entity_type' => 'auth', 'outcome' => 'allowed'],
            ['action' => 'login_failed', 'entity_type' => 'auth', 'outcome' => 'denied'],
            ['action' => 'create', 'entity_type' => 'business', 'outcome' => 'allowed'],
            ['action' => 'update', 'entity_type' => 'appointment', 'outcome' => 'allowed'],
            ['action' => 'delete', 'entity_type' => 'contact', 'outcome' => 'allowed'],
            ['action' => 'guard_divergence', 'entity_type' => 'ical_feed', 'outcome' => null],
            ['action' => 'anonymize', 'entity_type' => 'database', 'outcome' => 'allowed'],
        ];

        for ($i = 0; $i < 60; $i++) {
            $template = $actions[$i % count($actions)];
            $entries[] = [
                'actor_id'       => ($i % 3 === 0) ? null : (($i % 2) + 1),
                'entity_type'    => $template['entity_type'],
                'entity_id'      => (string) (($i % 5) + 1),
                'action'         => $template['action'],
                'correlation_id' => 'corr-console-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'context'        => json_encode([
                    'outcome' => $template['outcome'],
                    'ip_hash' => hash('sha256', 'fixture-ip-' . $i),
                ]),
                'created_at'     => $now->copy()->subMinutes($i * 15),
            ];
        }

        DB::table('audit_logs')->insert($entries);
    }
}
