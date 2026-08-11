<?php

namespace App\TG;

use Illuminate\Support\Facades\DB;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class ContactEraser
{
    public function erase(Contact $contact, Business $business): array
    {
        $result = [
            'contact_id' => $contact->id,
            'erased_fields' => [],
            'limitations' => [],
            'fully_deleted' => false,
        ];

        $hasFutureAppointments = DB::table('appointments')
            ->where('contact_id', $contact->id)
            ->where('start_at', '>=', now())
            ->whereNull('deleted_at')
            ->exists();

        if ($hasFutureAppointments) {
            $result['limitations'][] = 'Contact has future appointments; record retained with PII erased.';
        }

        $otherBusinessLinks = $contact->businesses()
            ->where('business_id', '!=', $business->id)
            ->count();

        DB::transaction(function () use ($contact, $business, &$result, $otherBusinessLinks) {
            DB::table('contacts')
                ->where('id', $contact->id)
                ->update([
                    'nin' => null,
                    'nin_hash' => null,
                    'mobile' => null,
                    'mobile_hash' => null,
                    'birthdate' => null,
                    'email' => null,
                    'postal_address' => null,
                    'occupation' => null,
                ]);

            $result['erased_fields'] = ['nin', 'mobile', 'birthdate', 'email', 'postal_address', 'occupation'];

            $business->contacts()->detach($contact->id);

            if ($otherBusinessLinks === 0) {
                $hasAnyReferences = DB::table('appointments')
                    ->where('contact_id', $contact->id)
                    ->exists();

                if ($hasAnyReferences) {
                    DB::table('contacts')
                        ->where('id', $contact->id)
                        ->update([
                            'firstname' => '[erased]',
                            'lastname' => '[erased]',
                            'deleted_at' => now(),
                        ]);
                    $result['limitations'][] = 'Historical appointment references prevent full deletion; record soft-deleted with all PII erased.';
                } else {
                    DB::table('contacts')->where('id', $contact->id)->delete();
                    $result['fully_deleted'] = true;
                }
            } else {
                $result['limitations'][] = "Contact is linked to {$otherBusinessLinks} other business(es); only this business link and PII removed.";
            }
        });

        return $result;
    }
}
