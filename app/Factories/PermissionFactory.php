<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Illuminate\Support\Str;

class PermissionFactory
{
    public static function definition(Faker $faker): array
    {
        $name = $faker->word;

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $faker->sentence,
        ];
    }
}
