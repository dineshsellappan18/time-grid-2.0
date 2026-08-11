<?php

namespace App\Console\Commands;

use App\TG\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnonymizeDatabase extends Command
{
    protected $signature = 'db:anonymize
        {--dry-run : Report what would change without writing}
        {--limit=0 : Limit rows processed per table (0 = all)}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Anonymize PII fields in a non-production database snapshot for safe staging use';

    private const CHUNK_SIZE = 1000;

    private string $pepper;

    private array $report = [];

    public function handle(AuditLogger $audit): int
    {
        if (app()->environment('production')) {
            $this->error('This command refuses to run in production.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('This will irreversibly anonymize PII in the current database. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $this->pepper = config('app.key');
        $isDryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($isDryRun) {
            $this->info('[DRY RUN] No data will be modified.');
        }

        $this->anonymizeContacts($isDryRun, $limit);
        $this->anonymizeUsers($isDryRun, $limit);
        $this->anonymizeAppointments($isDryRun, $limit);
        $this->clearOAuthIdentities($isDryRun, $limit);

        $this->printReport();

        if (!$isDryRun) {
            $audit->append(
                action: 'db.anonymize',
                resourceType: 'database',
                resourceId: config('database.connections.' . config('database.default') . '.database', 'unknown'),
                outcome: 'success',
                changes: $this->report,
            );
        }

        Log::info('db:anonymize completed', [
            'dry_run' => $isDryRun,
            'report'  => $this->report,
        ]);

        return self::SUCCESS;
    }

    private function anonymizeContacts(bool $isDryRun, int $limit): void
    {
        $table = 'contacts';
        $scanned = 0;
        $changed = 0;

        $query = DB::table($table);

        $query->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($table, $isDryRun, $limit, &$scanned, &$changed) {
            foreach ($rows as $row) {
                if ($limit > 0 && $scanned >= $limit) {
                    return false;
                }

                $scanned++;
                $id = $row->id;

                $updates = [];

                if (!empty($row->email)) {
                    $updates['email'] = $this->pseudonymizeEmail($row->email, $id, $table);
                }

                if (!empty($row->nin)) {
                    $updates['nin'] = $this->hashField($row->nin, $id, 'nin');
                }

                if (!empty($row->mobile)) {
                    $updates['mobile'] = $this->pseudonymizePhone($id);
                }

                if (!empty($row->birthdate)) {
                    $updates['birthdate'] = '1900-01-01';
                }

                if (isset($row->postal_address) && $row->postal_address !== null && $row->postal_address !== '') {
                    $updates['postal_address'] = 'REDACTED';
                }

                if (!empty($updates)) {
                    $changed++;
                    if (!$isDryRun) {
                        DB::table($table)->where('id', $id)->update($updates);
                    }
                }
            }
        });

        $this->report[$table] = ['scanned' => $scanned, 'changed' => $changed];
        $this->info("  {$table}: scanned={$scanned}, changed={$changed}");
    }

    private function anonymizeUsers(bool $isDryRun, int $limit): void
    {
        $table = 'users';
        $scanned = 0;
        $changed = 0;

        DB::table($table)->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($table, $isDryRun, $limit, &$scanned, &$changed) {
            foreach ($rows as $row) {
                if ($limit > 0 && $scanned >= $limit) {
                    return false;
                }

                $scanned++;
                $id = $row->id;

                $updates = [];

                if (!empty($row->email)) {
                    $updates['email'] = $this->pseudonymizeEmail($row->email, $id, $table);
                }

                if (!empty($row->last_ip)) {
                    $updates['last_ip'] = '0.0.0.0';
                }

                if (!empty($updates)) {
                    $changed++;
                    if (!$isDryRun) {
                        DB::table($table)->where('id', $id)->update($updates);
                    }
                }
            }
        });

        $this->report[$table] = ['scanned' => $scanned, 'changed' => $changed];
        $this->info("  {$table}: scanned={$scanned}, changed={$changed}");
    }

    private function anonymizeAppointments(bool $isDryRun, int $limit): void
    {
        $table = 'appointments';
        $scanned = 0;
        $changed = 0;

        DB::table($table)->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($table, $isDryRun, $limit, &$scanned, &$changed) {
            foreach ($rows as $row) {
                if ($limit > 0 && $scanned >= $limit) {
                    return false;
                }

                $scanned++;
                $id = $row->id;

                $updates = [];

                if (isset($row->comments) && $row->comments !== null && $row->comments !== '') {
                    $updates['comments'] = 'REDACTED';
                }

                if (!empty($updates)) {
                    $changed++;
                    if (!$isDryRun) {
                        DB::table($table)->where('id', $id)->update($updates);
                    }
                }
            }
        });

        $this->report[$table] = ['scanned' => $scanned, 'changed' => $changed];
        $this->info("  {$table}: scanned={$scanned}, changed={$changed}");
    }

    private function clearOAuthIdentities(bool $isDryRun, int $limit): void
    {
        $table = 'users';
        $scanned = 0;
        $changed = 0;

        $hasOAuthColumns = DB::getSchemaBuilder()->hasColumn($table, 'oauth_id');
        if (!$hasOAuthColumns) {
            $this->report['oauth'] = ['scanned' => 0, 'changed' => 0, 'note' => 'no oauth columns found'];
            $this->info("  oauth: skipped (no oauth_id column)");
            return;
        }

        DB::table($table)
            ->whereNotNull('oauth_id')
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($rows) use ($table, $isDryRun, $limit, &$scanned, &$changed) {
                foreach ($rows as $row) {
                    if ($limit > 0 && $scanned >= $limit) {
                        return false;
                    }

                    $scanned++;
                    $changed++;

                    if (!$isDryRun) {
                        DB::table($table)->where('id', $row->id)->update([
                            'oauth_id'    => null,
                            'oauth_token' => null,
                        ]);
                    }
                }
            });

        $this->report['oauth'] = ['scanned' => $scanned, 'changed' => $changed];
        $this->info("  oauth: scanned={$scanned}, changed={$changed}");
    }

    private function pseudonymizeEmail(string $original, int $id, string $table): string
    {
        $hash = substr(hash('sha256', $this->pepper . $table . $id . $original), 0, 8);
        return "anon_{$hash}@example.invalid";
    }

    private function pseudonymizePhone(int $id): string
    {
        $suffix = str_pad((string) ($id % 10000000), 7, '0', STR_PAD_LEFT);
        return "+1555{$suffix}";
    }

    private function hashField(string $value, int $id, string $field): string
    {
        return substr(hash('sha256', $this->pepper . $field . $id . $value), 0, 16);
    }

    private function printReport(): void
    {
        $this->newLine();
        $this->info('=== Anonymization Report ===');

        $headers = ['Table', 'Scanned', 'Changed'];
        $rows = [];
        foreach ($this->report as $table => $data) {
            $rows[] = [$table, $data['scanned'], $data['changed']];
        }

        $this->table($headers, $rows);
    }
}
