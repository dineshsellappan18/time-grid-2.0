<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Timegridio\Concierge\Models\Business;

class ServiceFactory
{
    public static function definition(Faker $faker)
    {
        return [
            'business_id'   => factory(Business::class)->create()->id,
            'name'          => $faker->sentence(2),
            'description'   => $faker->paragraph,
            'prerequisites' => $faker->paragraph,
            'duration'      => $faker->randomElement([15, 30, 60, 120]),
        ];
    }
}
