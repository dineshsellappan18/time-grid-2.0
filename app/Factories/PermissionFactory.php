<?php

namespace App\Factories;

use Faker\Generator as Faker;

class PermissionFactory
{
    public static function definition(Faker $faker)
    {
        $name = $faker->word;

        return [
            'name'        => $name,
            'slug'        => str_slug($name),
            'description' => $faker->sentence,
        ];
    }
}
