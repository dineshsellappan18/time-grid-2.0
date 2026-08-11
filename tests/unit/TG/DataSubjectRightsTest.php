<?php

namespace Tests\Unit\TG;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\TG\ContactExportAssembler;
use App\TG\ContactEraser;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Business;
use Carbon\Carbon;

class DataSubjectRightsTest extends TestCase
{
    use DatabaseTransactions;

    private ContactExportAssembler $exporter;
    private ContactEraser $eraser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exporter = new ContactExportAssembler();
        $this->eraser = new ContactEraser();
    }

    private function seedContact(array $overrides = []): Contact
    {
        $id = DB::table('contacts')->insertGetId(array_merge([
            'firstname'       => 'Export',
            'lastname'        => 'Test',
            'gender'          => 'F',
            'nin'             => Crypt::encryptString('NIN-EXPORT-1'),
            'nin_hash'        => 'hash1',
            'mobile'          => Crypt::encryptString('+44TEST'),
            'mobile_hash'     => 'hash2',
            'birthdate'       => Crypt::encryptString('1990-05-15'),
            'email'           => 'export@test.com',
            'postal_address'  => '123 Test St',
            'pii_backfilled_at' => now(),
            'created_at'      => now()->subYear(),
            'updated_at'      => now(),
        ], $overrides));

        return Contact::find($id);
    }

    private function seedBusiness(): Business
    {
        $id = DB::table('businesses')->insertGetId([
            'name' => 'Test Biz',
            'slug' => 'test-biz-' . uniqid(),
            'description' => 'Test',
            'timezone' => 'UTC',
            'strategy' => 'timeslot',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Business::find($id);
    }

    public function test_export_assembles_subject_fields(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();

        $data = $this->exporter->assemble($contact, $business);

        $this->assertArrayHasKey('subject', $data);
        $this->assertEquals('Export', $data['subject']['firstname']);
        $this->assertEquals('Test', $data['subject']['lastname']);
        $this->assertEquals('export@test.com', $data['subject']['email']);
    }

    public function test_export_scopes_appointments_to_business(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();
        $otherBusiness = $this->seedBusiness();

        DB::table('appointments')->insert([
            'business_id' => $business->id,
            'contact_id'  => $contact->id,
            'service_id'  => 1,
            'start_at'    => now()->subMonth(),
            'finish_at'   => now()->subMonth()->addHour(),
            'duration'    => 60,
            'status'      => 'C',
            'hash'        => md5('biz1'),
            'created_at'  => now()->subMonth(),
            'updated_at'  => now()->subMonth(),
        ]);

        DB::table('appointments')->insert([
            'business_id' => $otherBusiness->id,
            'contact_id'  => $contact->id,
            'service_id'  => 1,
            'start_at'    => now()->subWeek(),
            'finish_at'   => now()->subWeek()->addHour(),
            'duration'    => 60,
            'status'      => 'C',
            'hash'        => md5('biz2'),
            'created_at'  => now()->subWeek(),
            'updated_at'  => now()->subWeek(),
        ]);

        $data = $this->exporter->assemble($contact, $business);

        $this->assertCount(1, $data['appointments']);
    }

    public function test_export_with_no_appointments(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();

        $data = $this->exporter->assemble($contact, $business);

        $this->assertArrayHasKey('appointments', $data);
        $this->assertEmpty($data['appointments']);
    }

    public function test_export_excludes_internal_identifiers(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();

        $data = $this->exporter->assemble($contact, $business);

        $this->assertArrayNotHasKey('id', $data['subject']);
        $this->assertArrayNotHasKey('user_id', $data['subject']);
        $this->assertArrayNotHasKey('nin_hash', $data['subject']);
        $this->assertArrayNotHasKey('mobile_hash', $data['subject']);
    }

    public function test_erase_removes_restricted_fields(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();
        $business->contacts()->attach($contact->id, ['created_at' => now()]);

        $result = $this->eraser->erase($contact, $business);

        $this->assertContains('nin', $result['erased_fields']);
        $this->assertContains('mobile', $result['erased_fields']);
        $this->assertContains('birthdate', $result['erased_fields']);

        $row = DB::table('contacts')->find($contact->id);
        if ($row) {
            $this->assertNull($row->nin);
            $this->assertNull($row->mobile);
            $this->assertNull($row->birthdate);
        }
    }

    public function test_erase_is_idempotent(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();
        $business->contacts()->attach($contact->id, ['created_at' => now()]);

        $result1 = $this->eraser->erase($contact, $business);
        $this->assertNotEmpty($result1['erased_fields']);
    }

    public function test_erase_respects_multi_business_scope(): void
    {
        $contact = $this->seedContact();
        $business1 = $this->seedBusiness();
        $business2 = $this->seedBusiness();
        $business1->contacts()->attach($contact->id, ['created_at' => now()]);
        $business2->contacts()->attach($contact->id, ['created_at' => now()]);

        $result = $this->eraser->erase($contact, $business1);

        $this->assertFalse($result['fully_deleted']);
        $this->assertNotEmpty($result['limitations']);

        $stillLinked = $business2->contacts()->where('contacts.id', $contact->id)->exists();
        $this->assertTrue($stillLinked);
    }

    public function test_erase_blocks_with_future_appointment_info(): void
    {
        $contact = $this->seedContact();
        $business = $this->seedBusiness();
        $business->contacts()->attach($contact->id, ['created_at' => now()]);

        DB::table('appointments')->insert([
            'business_id' => $business->id,
            'contact_id'  => $contact->id,
            'service_id'  => 1,
            'start_at'    => now()->addWeek(),
            'finish_at'   => now()->addWeek()->addHour(),
            'duration'    => 60,
            'status'      => 'R',
            'hash'        => md5('future'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $result = $this->eraser->erase($contact, $business);

        $this->assertNotEmpty($result['erased_fields']);
        $this->assertNotEmpty($result['limitations']);
    }
}
