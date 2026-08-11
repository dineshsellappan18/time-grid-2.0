<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Timegridio\Concierge\Models\Contact;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class ContactEncryptionCastTest extends TestCase
{
    public function test_nin_is_encrypted_on_set_and_decrypted_on_get(): void
    {
        $contact = new Contact();
        $contact->nin = '12345678';

        $raw = $contact->getAttributes()['nin'];
        $this->assertNotEquals('12345678', $raw);
        $this->assertEquals('12345678', Crypt::decryptString($raw));
        $this->assertEquals('12345678', $contact->nin);
    }

    public function test_mobile_is_encrypted_on_set_and_decrypted_on_get(): void
    {
        $contact = new Contact();
        $contact->mobile = '+1234567890';

        $raw = $contact->getAttributes()['mobile'];
        $this->assertNotEquals('+1234567890', $raw);
        $this->assertEquals('+1234567890', Crypt::decryptString($raw));
        $this->assertEquals('+1234567890', $contact->mobile);
    }

    public function test_birthdate_is_encrypted_on_set_and_decrypted_on_get(): void
    {
        $contact = new Contact();
        $contact->birthdate = Carbon::parse('1990-05-15');

        $raw = $contact->getAttributes()['birthdate'];
        $this->assertNotEquals('1990-05-15', $raw);
        $this->assertEquals('1990-05-15', Crypt::decryptString($raw));
        $this->assertInstanceOf(Carbon::class, $contact->birthdate);
        $this->assertEquals('1990-05-15', $contact->birthdate->toDateString());
    }

    public function test_null_nin_stays_null(): void
    {
        $contact = new Contact();
        $contact->nin = null;

        $this->assertNull($contact->getAttributes()['nin']);
        $this->assertNull($contact->nin);
    }

    public function test_empty_nin_becomes_null(): void
    {
        $contact = new Contact();
        $contact->nin = '   ';

        $this->assertNull($contact->getAttributes()['nin']);
    }

    public function test_null_mobile_stays_null(): void
    {
        $contact = new Contact();
        $contact->mobile = null;

        $this->assertNull($contact->getAttributes()['mobile']);
        $this->assertNull($contact->mobile);
    }

    public function test_null_birthdate_stays_null(): void
    {
        $contact = new Contact();
        $contact->birthdate = null;

        $this->assertNull($contact->getAttributes()['birthdate']);
        $this->assertNull($contact->birthdate);
    }

    public function test_non_ascii_nin_round_trips(): void
    {
        $contact = new Contact();
        $contact->nin = 'Ä-2024/ñ';

        $this->assertEquals('Ä-2024/ñ', $contact->nin);
    }

    public function test_blind_index_is_deterministic(): void
    {
        $hash1 = Contact::computeBlindIndex('12345678');
        $hash2 = Contact::computeBlindIndex('12345678');

        $this->assertNotNull($hash1);
        $this->assertEquals($hash1, $hash2);
    }

    public function test_blind_index_normalizes_formatting(): void
    {
        $hash1 = Contact::computeBlindIndex('+1-234-567-890');
        $hash2 = Contact::computeBlindIndex('1234567890');

        $this->assertEquals($hash1, $hash2);
    }

    public function test_blind_index_returns_null_for_null_input(): void
    {
        $this->assertNull(Contact::computeBlindIndex(null));
        $this->assertNull(Contact::computeBlindIndex(''));
        $this->assertNull(Contact::computeBlindIndex('   '));
    }

    public function test_nin_hash_set_on_mutation(): void
    {
        $contact = new Contact();
        $contact->nin = 'AB-12345';

        $this->assertNotNull($contact->getAttributes()['nin_hash']);
        $this->assertEquals(64, strlen($contact->getAttributes()['nin_hash']));
    }

    public function test_mobile_hash_set_on_mutation(): void
    {
        $contact = new Contact();
        $contact->mobile = '+447911123456';

        $this->assertNotNull($contact->getAttributes()['mobile_hash']);
        $this->assertEquals(64, strlen($contact->getAttributes()['mobile_hash']));
    }

    public function test_decryption_failure_for_nin_returns_raw_plaintext(): void
    {
        $contact = new Contact();
        $contact->attributes['nin'] = 'not-encrypted-value-short';

        $this->assertEquals('not-encrypted-value-short', $contact->nin);
    }

    public function test_decryption_failure_for_mobile_returns_raw_plaintext(): void
    {
        $contact = new Contact();
        $contact->attributes['mobile'] = '+1234567890';

        $this->assertEquals('+1234567890', $contact->mobile);
    }
}
