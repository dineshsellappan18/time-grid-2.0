<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Contact;

class EncryptContactPii extends Command
{
    protected $signature = 'contacts:encrypt-pii
        {--chunk=1000 : Number of rows per batch}
        {--limit=0 : Max rows to process (0 = all)}
        {--dry-run : Preview without writing}';

    protected $description = 'Encrypt restricted contact PII (nin, mobile, birthdate) at rest. Idempotent and resumable via pii_backfilled_at marker.';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $this->info($dryRun ? '[DRY RUN] Previewing PII encryption...' : 'Starting PII encryption backfill...');

        $query = DB::table('contacts')
            ->whereNull('pii_backfilled_at')
            ->orderBy('id');

        $shouldStop = false;

        $query->chunkById($chunkSize, function ($contacts) use (&$processed, &$skipped, &$failed, $dryRun, $limit, &$shouldStop) {
            if ($shouldStop) {
                return false;
            }

            foreach ($contacts as $contact) {
                if ($limit > 0 && $processed >= $limit) {
                    $shouldStop = true;
                    return false;
                }

                if ($contact->pii_backfilled_at !== null) {
                    $skipped++;
                    continue;
                }

                try {
                    $updates = $this->buildEncryptedRow($contact);

                    if ($dryRun) {
                        $this->line("  [DRY] Contact #{$contact->id}: would encrypt " . count(array_filter($updates, fn($v) => $v !== null)) . " fields");
                    } else {
                        DB::table('contacts')
                            ->where('id', $contact->id)
                            ->update($updates);
                    }

                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("  Contact #{$contact->id} failed: {$e->getMessage()}");

                    if ($failed > 10) {
                        $this->error('Too many failures, aborting. Resume by re-running the command.');
                        return false;
                    }
                }
            }
        });

        $summary = $dryRun ? '[DRY RUN] ' : '';
        $summary .= "Backfill complete: {$processed} processed, {$skipped} skipped, {$failed} failed.";
        $this->info($summary);

        if (!$dryRun) {
            Log::info('contacts.encrypt_pii.complete', [
                'actor'     => null,
                'resource'  => 'contacts',
                'operation' => 'encrypt_pii',
                'context'   => ['processed' => $processed, 'skipped' => $skipped, 'failed' => $failed],
            ]);
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildEncryptedRow(object $contact): array
    {
        $updates = ['pii_backfilled_at' => now()];

        if ($contact->nin !== null && !$this->isAlreadyEncrypted($contact->nin)) {
            $updates['nin'] = Crypt::encryptString($contact->nin);
            $updates['nin_hash'] = Contact::computeBlindIndex($contact->nin);
        }

        if ($contact->mobile !== null && !$this->isAlreadyEncrypted($contact->mobile)) {
            $updates['mobile'] = Crypt::encryptString($contact->mobile);
            $updates['mobile_hash'] = Contact::computeBlindIndex($contact->mobile);
        }

        if ($contact->birthdate !== null && !$this->isAlreadyEncrypted((string) $contact->birthdate)) {
            $updates['birthdate'] = Crypt::encryptString((string) $contact->birthdate);
        }

        return $updates;
    }

    private function isAlreadyEncrypted(string $value): bool
    {
        if (strlen($value) < 50) {
            return false;
        }

        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
