<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Timegridio\Concierge\Models\Business;

class ServiceTypeFactory
{
    public static function definition(Faker $faker)
    {
        return [
            'business_id' => factory(Business::class)->create()->id,
            'name'        => $faker->sentence(3),
            'description' => $faker->paragraph,
        ];
    }
}
