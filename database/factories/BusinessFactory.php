<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->sentence(3),
            'description'     => $this->faker->paragraph,
            'timezone'        => $this->faker->timezone,
            'postal_address'  => $this->faker->address,
            'phone'           => null,
            'social_facebook' => 'https://www.facebook.com/example?fref=ts',
            'strategy'        => 'dateslot',
            'plan'            => 'free',
            'category_id'     => CategoryFactory::new()->create()->id,
            'listed'          => true,
        ];
    }
}
