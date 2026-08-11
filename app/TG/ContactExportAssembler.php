<?php

namespace App\TG;

use Illuminate\Support\Facades\DB;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class ContactExportAssembler
{
    public function assemble(Contact $contact, Business $business): array
    {
        return [
            'subject' => $this->assembleSubject($contact),
            'appointments' => $this->assembleAppointments($contact, $business),
            'metadata' => [
                'exported_at' => now()->toIso8601String(),
                'business_id' => $business->id,
                'business_name' => $business->name,
                'format_version' => '1.0',
            ],
        ];
    }

    private function assembleSubject(Contact $contact): array
    {
        return [
            'firstname' => $contact->firstname,
            'lastname' => $contact->lastname,
            'email' => $contact->email,
            'nin' => $contact->nin,
            'mobile' => $contact->mobile,
            'mobile_country' => $contact->mobile_country,
            'birthdate' => $contact->birthdate?->toDateString(),
            'gender' => $contact->gender,
            'postal_address' => $contact->postal_address,
            'member_since' => $contact->created_at?->toIso8601String(),
        ];
    }

    private function assembleAppointments(Contact $contact, Business $business): array
    {
        $appointments = DB::table('appointments')
            ->where('contact_id', $contact->id)
            ->where('business_id', $business->id)
            ->select(['start_at', 'finish_at', 'duration', 'status', 'hash', 'created_at'])
            ->orderBy('start_at', 'desc')
            ->get();

        return $appointments->map(function ($appt) {
            return [
                'start_at' => $appt->start_at,
                'finish_at' => $appt->finish_at,
                'duration_minutes' => $appt->duration,
                'status' => $appt->status,
                'reference' => $appt->hash,
                'booked_at' => $appt->created_at,
            ];
        })->all();
    }
}
