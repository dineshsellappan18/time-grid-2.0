<?php

namespace App\Factories;

use Carbon\Carbon;
use Faker\Generator as Faker;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class AppointmentFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'business_id' => factory(Business::class)->create()->id,
            'contact_id'  => factory(Contact::class)->create()->id,
            'service_id'  => factory(Service::class)->create()->id,
            'vacancy_id'  => factory(Vacancy::class)->create()->id,
            'status'      => $faker->randomElement(['R', 'C', 'A', 'S']),
            'start_at'    => Carbon::parse(date('Y-m-d 08:00:00', strtotime('today +2 days'))),
            'duration'    => $faker->randomElement([15, 30, 60, 120]),
            'comments'    => $faker->sentence,
        ];
    }
}
