<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptContactPiiCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function seedPlaintextContact(array $overrides = []): int
    {
        return DB::table('contacts')->insertGetId(array_merge([
            'firstname' => 'Test',
            'lastname' => 'User',
            'gender' => 'M',
            'nin' => '12345678',
            'mobile' => '+1234567890',
            'birthdate' => '1990-01-15',
            'pii_backfilled_at' => null,
            'nin_hash' => null,
            'mobile_hash' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_dry_run_does_not_modify_data(): void
    {
        $id = $this->seedPlaintextContact();

        $this->artisan('contacts:encrypt-pii', ['--dry-run' => true])
            ->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('12345678', $row->nin);
        $this->assertNull($row->pii_backfilled_at);
    }

    public function test_backfill_encrypts_plaintext_fields(): void
    {
        $id = $this->seedPlaintextContact();

        $this->artisan('contacts:encrypt-pii')
            ->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertNotEquals('12345678', $row->nin);
        $this->assertEquals('12345678', Crypt::decryptString($row->nin));
        $this->assertNotNull($row->nin_hash);
        $this->assertNotNull($row->pii_backfilled_at);
    }

    public function test_backfill_is_idempotent(): void
    {
        $id = $this->seedPlaintextContact();

        $this->artisan('contacts:encrypt-pii')->assertExitCode(0);
        $firstRun = DB::table('contacts')->find($id);

        $this->artisan('contacts:encrypt-pii')->assertExitCode(0);
        $secondRun = DB::table('contacts')->find($id);

        $this->assertEquals($firstRun->nin, $secondRun->nin);
        $this->assertEquals($firstRun->pii_backfilled_at, $secondRun->pii_backfilled_at);
    }

    public function test_limit_option_caps_processing(): void
    {
        $this->seedPlaintextContact(['nin' => 'A1']);
        $this->seedPlaintextContact(['nin' => 'A2']);
        $this->seedPlaintextContact(['nin' => 'A3']);

        $this->artisan('contacts:encrypt-pii', ['--limit' => 1])
            ->assertExitCode(0);

        $unprocessed = DB::table('contacts')
            ->whereNull('pii_backfilled_at')
            ->count();
        $this->assertEquals(2, $unprocessed);
    }

    public function test_null_fields_handled_gracefully(): void
    {
        $id = $this->seedPlaintextContact([
            'nin' => null,
            'mobile' => null,
            'birthdate' => null,
        ]);

        $this->artisan('contacts:encrypt-pii')
            ->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertNull($row->nin);
        $this->assertNull($row->mobile);
        $this->assertNull($row->birthdate);
        $this->assertNotNull($row->pii_backfilled_at);
    }

    public function test_resumability_via_marker(): void
    {
        $id1 = $this->seedPlaintextContact(['nin' => 'FIRST']);
        $id2 = $this->seedPlaintextContact(['nin' => 'SECOND']);

        DB::table('contacts')->where('id', $id1)->update([
            'pii_backfilled_at' => now(),
            'nin' => Crypt::encryptString('FIRST'),
            'nin_hash' => 'already_hashed',
        ]);

        $this->artisan('contacts:encrypt-pii')->assertExitCode(0);

        $row1 = DB::table('contacts')->find($id1);
        $row2 = DB::table('contacts')->find($id2);

        $this->assertEquals('already_hashed', $row1->nin_hash);
        $this->assertNotEquals('SECOND', $row2->nin);
        $this->assertEquals('SECOND', Crypt::decryptString($row2->nin));
    }
}
