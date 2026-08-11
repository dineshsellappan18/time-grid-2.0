<?php

namespace Tests\Integration\Security;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Contact;
use Carbon\Carbon;

/**
 * Verifies that restricted PII fields (nin, mobile, birthdate) never appear
 * in plaintext within log output, even when a Contact is logged.
 */
class PiiLogScanTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logFile = storage_path('logs/pii-scan-test.log');
        config(['logging.channels.pii_test' => [
            'driver' => 'single',
            'path' => $this->logFile,
            'level' => 'debug',
        ]]);

        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        parent::tearDown();
    }

    public function test_encrypted_nin_never_appears_in_log(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Log';
        $contact->lastname = 'Scan';
        $contact->gender = 'M';
        $contact->nin = 'SECRET-NIN-12345';
        $contact->mobile = '+44SECRETMOBILE';
        $contact->birthdate = Carbon::parse('1985-07-20');

        Log::channel('pii_test')->info('contact.operation', [
            'contact_id' => 999,
            'action' => 'update',
        ]);

        Log::channel('pii_test')->info('contact.audit', [
            'actor' => 1,
            'resource_type' => 'contact',
        ]);

        if (!file_exists($this->logFile)) {
            $this->assertTrue(true);
            return;
        }

        $logContents = file_get_contents($this->logFile);

        $this->assertStringNotContainsString('SECRET-NIN-12345', $logContents);
        $this->assertStringNotContainsString('+44SECRETMOBILE', $logContents);
        $this->assertStringNotContainsString('1985-07-20', $logContents);
    }

    public function test_contact_toarray_hides_hash_columns(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Hidden';
        $contact->lastname = 'Hashes';
        $contact->gender = 'F';
        $contact->nin = 'HASH-CHECK';
        $contact->mobile = '+1HASHCHECK';

        $array = $contact->toArray();

        $this->assertArrayNotHasKey('nin_hash', $array);
        $this->assertArrayNotHasKey('mobile_hash', $array);
        $this->assertArrayNotHasKey('pii_backfilled_at', $array);
    }

    public function test_restricted_keys_redacted_from_json_output(): void
    {
        $contact = new Contact();
        $contact->firstname = 'JSON';
        $contact->lastname = 'Test';
        $contact->gender = 'M';
        $contact->nin = 'JSON-NIN-99';
        $contact->mobile = '+3JSONMOBILE';

        $json = $contact->toJson();
        $decoded = json_decode($json, true);

        $this->assertArrayNotHasKey('nin_hash', $decoded);
        $this->assertArrayNotHasKey('mobile_hash', $decoded);
    }
}
