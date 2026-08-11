<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\ServiceType;

class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        return [
            'business_id' => BusinessFactory::new()->create()->id,
            'name'        => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
        ];
    }
}
