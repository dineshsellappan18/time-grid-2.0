<?php

namespace App\Factories;

use Faker\Generator as Faker;
use Timegridio\Concierge\Models\Category;

class BusinessFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            'name'            => $faker->sentence(3),
            'description'     => $faker->paragraph,
            'timezone'        => $faker->timezone,
            'postal_address'  => $faker->address,
            'phone'           => null,
            'social_facebook' => 'https://www.facebook.com/example?fref=ts',
            'strategy'        => 'dateslot',
            'plan'            => 'free',
            'category_id'     => factory(Category::class)->create()->id,
            'listed'          => true,
        ];
    }
}
