<?php

namespace Tests\Integration\ContactPii;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Timegridio\Concierge\Models\Contact;
use Carbon\Carbon;

class ContactPiiEncryptionIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_contact_stores_ciphertext_in_database(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Jane';
        $contact->lastname = 'Doe';
        $contact->gender = 'F';
        $contact->nin = '99887766';
        $contact->mobile = '+4407911222333';
        $contact->birthdate = Carbon::parse('1985-03-10');
        $contact->save();

        $raw = DB::table('contacts')->where('id', $contact->id)->first();

        $this->assertNotEquals('99887766', $raw->nin);
        $this->assertNotEquals('+4407911222333', $raw->mobile);
        $this->assertNotEquals('1985-03-10', $raw->birthdate);

        $this->assertEquals('99887766', Crypt::decryptString($raw->nin));
        $this->assertEquals('+4407911222333', Crypt::decryptString($raw->mobile));
        $this->assertEquals('1985-03-10', Crypt::decryptString($raw->birthdate));
    }

    public function test_read_contact_returns_plaintext_values(): void
    {
        $contact = new Contact();
        $contact->firstname = 'John';
        $contact->lastname = 'Smith';
        $contact->gender = 'M';
        $contact->nin = '55443322';
        $contact->mobile = '+33612345678';
        $contact->birthdate = Carbon::parse('1992-11-25');
        $contact->save();

        $loaded = Contact::find($contact->id);
        $this->assertEquals('55443322', $loaded->nin);
        $this->assertEquals('+33612345678', $loaded->mobile);
        $this->assertEquals('1992-11-25', $loaded->birthdate->toDateString());
    }

    public function test_update_contact_re_encrypts(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Test';
        $contact->lastname = 'User';
        $contact->gender = 'M';
        $contact->nin = 'OLD-NIN-001';
        $contact->save();

        $contact->nin = 'NEW-NIN-002';
        $contact->save();

        $raw = DB::table('contacts')->where('id', $contact->id)->first();
        $this->assertEquals('NEW-NIN-002', Crypt::decryptString($raw->nin));
    }

    public function test_blind_index_enables_exact_match_search(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Search';
        $contact->lastname = 'Test';
        $contact->gender = 'F';
        $contact->nin = 'FINDME-123';
        $contact->save();

        $hash = Contact::computeBlindIndex('FINDME-123');
        $found = Contact::where('nin_hash', $hash)->first();

        $this->assertNotNull($found);
        $this->assertEquals($contact->id, $found->id);
    }

    public function test_mobile_blind_index_enables_search(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Mobile';
        $contact->lastname = 'Finder';
        $contact->gender = 'F';
        $contact->mobile = '+49151SEARCH';
        $contact->save();

        $hash = Contact::computeBlindIndex('+49151SEARCH');
        $found = Contact::where('mobile_hash', $hash)->first();

        $this->assertNotNull($found);
        $this->assertEquals($contact->id, $found->id);
    }

    public function test_raw_query_returns_no_plaintext_pii(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Plaintext';
        $contact->lastname = 'Check';
        $contact->gender = 'M';
        $contact->nin = 'SHOULD-NOT-APPEAR';
        $contact->mobile = '+1555NODISPLAY';
        $contact->birthdate = Carbon::parse('2000-01-01');
        $contact->save();

        $raw = DB::table('contacts')->where('id', $contact->id)->first();

        $this->assertStringNotContainsString('SHOULD-NOT-APPEAR', $raw->nin);
        $this->assertStringNotContainsString('+1555NODISPLAY', $raw->mobile);
        $this->assertStringNotContainsString('2000-01-01', $raw->birthdate);
    }

    public function test_null_pii_fields_remain_null(): void
    {
        $contact = new Contact();
        $contact->firstname = 'Nullable';
        $contact->lastname = 'Fields';
        $contact->gender = 'F';
        $contact->nin = null;
        $contact->mobile = null;
        $contact->birthdate = null;
        $contact->save();

        $raw = DB::table('contacts')->where('id', $contact->id)->first();
        $this->assertNull($raw->nin);
        $this->assertNull($raw->mobile);
        $this->assertNull($raw->birthdate);
    }
}
