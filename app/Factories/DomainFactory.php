<?php

namespace App\Factories;

use App\Models\User;
use Faker\Generator as Faker;

class DomainFactory
{
    public static function definition(Faker $faker)
    {
        return [
            'slug'     => str_slug($faker->name),
            'owner_id' => factory(User::class)->create()->id,
        ];
    }
}
