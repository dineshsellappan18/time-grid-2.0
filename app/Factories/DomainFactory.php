<?php

namespace App\Factories;

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class DomainFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'slug'     => Str::slug($faker->name),
            'owner_id' => factory(User::class)->create()->id,
        ];
    }
}
