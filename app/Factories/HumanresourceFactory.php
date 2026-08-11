<?php

namespace App\Factories;

use Faker\Generator as Faker;

class HumanresourceFactory
{
    public static function definition(Faker $faker)
    {
        return [
            'name'     => $faker->firstName,
            'capacity' => 1,
        ];
    }
}
