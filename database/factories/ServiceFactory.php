<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'business_id'   => BusinessFactory::new()->create()->id,
            'name'          => $this->faker->sentence(2),
            'description'   => $this->faker->paragraph,
            'prerequisites' => $this->faker->paragraph,
            'duration'      => $this->faker->randomElement([15, 30, 60, 120]),
        ];
    }
}
