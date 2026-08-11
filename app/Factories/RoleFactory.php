<?php

namespace App\Factories;

use Faker\Generator as Faker;

class RoleFactory
{
    public static function definition(Faker $faker)
    {
        $name = $faker->word;

        return [
            'name'        => $faker->word,
            'slug'        => str_slug($name),
            'description' => $faker->sentence,
        ];
    }
}
