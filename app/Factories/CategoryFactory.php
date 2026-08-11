<?php

namespace App\Factories;

use Faker\Generator as Faker;

class CategoryFactory
{
    public static function definition(Faker $faker)
    {
        return [
            'name'        => $faker->sentence(3),
            'slug'        => str_slug($faker->name),
            'description' => $faker->paragraph,
            'strategy'    => 'dateslot',
        ];
    }
}
