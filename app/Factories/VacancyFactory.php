<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class VacancyFactory
{
    public static function definition(Faker $faker)
    {
        $date = $faker->dateTimeBetween('today', 'today +7 days')->format('Y-m-d');

        return [
            'business_id' => factory(Business::class)->create()->id,
            'service_id'  => factory(Service::class)->create()->id,
            'date'        => date('Y-m-d', strtotime($date)),
            'start_at'    => date('Y-m-d 00:00:00', strtotime($date)),
            'finish_at'   => date('Y-m-d 23:00:00', strtotime($date)),
            'capacity'    => 1,
        ];
    }
}
