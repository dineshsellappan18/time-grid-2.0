<?php

namespace App\Factories;

use Carbon\Carbon;
use Faker\Generator as Faker;

class ContactFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'firstname'      => str_pad($faker->firstName, 3, '_'),
            'lastname'       => str_pad($faker->lastName, 3, '_'),
            'nin'            => $faker->numberBetween(25000000, 50000000),
            'email'          => $faker->unique()->safeEmail,
            'birthdate'      => Carbon::now()->subYears(30),
            'mobile'         => null,
            'mobile_country' => null,
            'gender'         => $faker->randomElement(['M', 'F']),
            'occupation'     => $faker->title,
            'martial_status' => null,
            'postal_address' => $faker->address,
        ];
    }
}
