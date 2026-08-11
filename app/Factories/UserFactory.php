<?php

namespace App\Factories;

use Faker\Generator as Faker;

class UserFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'username' => $faker->unique()->firstName,
            'name'     => $faker->firstName,
            'email'    => $faker->unique()->safeEmail,
            'password' => bcrypt('password'),
        ];
    }
}
