<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Illuminate\Support\Str;

class RoleFactory
{
    public static function definition(Faker $faker): array
    {
        $name = $faker->word;

        return [
            'name'        => $faker->word,
            'slug'        => Str::slug($name),
            'description' => $faker->sentence,
        ];
    }
}
