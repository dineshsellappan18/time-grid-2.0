<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Illuminate\Support\Str;

class CategoryFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'name'        => $faker->sentence(3),
            'slug'        => Str::slug($faker->name),
            'description' => $faker->paragraph,
            'strategy'    => 'dateslot',
        ];
    }
}
