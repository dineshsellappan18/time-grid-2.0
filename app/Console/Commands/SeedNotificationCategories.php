<?php

namespace App\Console\Commands;

use App\TG\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeedNotificationCategories extends Command
{
    protected $signature = 'notifications:seed-categories
        {--dry-run : Report what would change without writing}
        {--limit=0 : Limit categories seeded (0 = all)}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Seed required notification categories idempotently (safe to re-run)';

    private const REQUIRED_CATEGORIES = [
        'user.visitedShowroom'             => '{from.username} visited showroom',
        'user.registeredBusiness'          => '{from.username} registered :business',
        'user.subscribedBusiness'          => '{from.username} subscribed to business',
        'user.checkingVacancies'           => '{from.username} checks vacancies',
        'user.updatedBusinessPreferences'  => '{from.username} updated :business preferences',
        'user.importedContacts'            => '{from.username} imported :count contacts',
        'appointment.reserve'              => '{from.username} made a reservation for :business',
        'appointment.cancel'               => '{from.username} canceled appointment',
        'appointment.confirm'              => '{from.username} confirmed appointment',
        'appointment.serve'                => '{from.username} served appointment',
    ];

    public function handle(AuditLogger $audit): int
    {
        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('This will seed notification categories. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $isDryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($isDryRun) {
            $this->info('[DRY RUN] No data will be modified.');
        }

        $beforeCount = DB::table('notification_categories')->count();

        $created = 0;
        $skipped = 0;
        $processed = 0;

        foreach (self::REQUIRED_CATEGORIES as $name => $text) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $processed++;

            $exists = DB::table('notification_categories')
                ->where('name', $name)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            if (!$isDryRun) {
                DB::table('notification_categories')->insert([
                    'name' => $name,
                    'text' => $text,
                ]);
            }

            $created++;
        }

        $afterCount = $isDryRun ? $beforeCount : DB::table('notification_categories')->count();

        $this->newLine();
        $this->info('=== Notification Categories Seed Report ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Categories before', $beforeCount],
                ['Processed', $processed],
                ['Created', $created],
                ['Skipped (already exist)', $skipped],
                ['Categories after', $afterCount],
            ]
        );

        if (!$isDryRun) {
            $audit->append(
                action: 'notifications.seed_categories',
                resourceType: 'notification_categories',
                resourceId: 'batch',
                outcome: 'success',
                changes: [
                    'before_count' => $beforeCount,
                    'created'      => $created,
                    'skipped'      => $skipped,
                    'after_count'  => $afterCount,
                ],
            );
        }

        Log::info('notifications:seed-categories completed', [
            'dry_run'      => $isDryRun,
            'before_count' => $beforeCount,
            'created'      => $created,
            'skipped'      => $skipped,
            'after_count'  => $afterCount,
        ]);

        return self::SUCCESS;
    }
}
