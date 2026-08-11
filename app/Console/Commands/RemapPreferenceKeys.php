<?php

namespace App\Console\Commands;

use App\TG\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemapPreferenceKeys extends Command
{
    protected $signature = 'preferences:remap-keys
        {--dry-run : Report what would change without writing}
        {--limit=0 : Limit rows processed (0 = all)}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Remap legacy preference keys to their current equivalents (idempotent, resumable)';

    private const CHUNK_SIZE = 1000;

    private const KEY_MAP = [
        'appointment_annulation_pre_hs' => 'appointment_cancellation_pre_hs',
        'annulation_policy_advice'      => 'cancellation_policy_advice',
    ];

    public function handle(AuditLogger $audit): int
    {
        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('This will remap legacy preference keys. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $isDryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($isDryRun) {
            $this->info('[DRY RUN] No data will be modified.');
        }

        $legacyKeys = array_keys(self::KEY_MAP);

        $beforeCount = DB::table('preferences')
            ->whereIn('key', $legacyKeys)
            ->count();

        $this->info("Before: {$beforeCount} rows with legacy keys.");

        $processed = 0;
        $remapped = 0;

        DB::table('preferences')
            ->whereIn('key', $legacyKeys)
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($rows) use ($isDryRun, $limit, &$processed, &$remapped) {
                foreach ($rows as $row) {
                    if ($limit > 0 && $processed >= $limit) {
                        return false;
                    }

                    $processed++;
                    $newKey = self::KEY_MAP[$row->key] ?? null;

                    if ($newKey === null) {
                        continue;
                    }

                    $existingWithNewKey = DB::table('preferences')
                        ->where('key', $newKey)
                        ->where('preferenceable_id', $row->preferenceable_id)
                        ->where('preferenceable_type', $row->preferenceable_type)
                        ->exists();

                    if ($existingWithNewKey) {
                        if (!$isDryRun) {
                            DB::table('preferences')->where('id', $row->id)->delete();
                        }
                        $remapped++;
                        continue;
                    }

                    if (!$isDryRun) {
                        DB::table('preferences')
                            ->where('id', $row->id)
                            ->update(['key' => $newKey]);
                    }
                    $remapped++;
                }
            });

        $afterCount = $isDryRun ? $beforeCount : DB::table('preferences')
            ->whereIn('key', $legacyKeys)
            ->count();

        $this->newLine();
        $this->info('=== Preference Key Remap Report ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Legacy keys before', $beforeCount],
                ['Rows processed', $processed],
                ['Rows remapped', $remapped],
                ['Legacy keys after', $afterCount],
            ]
        );

        if (!$isDryRun) {
            $audit->append(
                action: 'preferences.remap_keys',
                resourceType: 'preferences',
                resourceId: 'batch',
                outcome: 'success',
                changes: [
                    'before_count' => $beforeCount,
                    'processed'    => $processed,
                    'remapped'     => $remapped,
                    'after_count'  => $afterCount,
                ],
            );
        }

        Log::info('preferences:remap-keys completed', [
            'dry_run'      => $isDryRun,
            'before_count' => $beforeCount,
            'processed'    => $processed,
            'remapped'     => $remapped,
            'after_count'  => $afterCount,
        ]);

        return self::SUCCESS;
    }
}
