<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Humanresource;

class HumanresourceFactory extends Factory
{
    protected $model = Humanresource::class;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->firstName,
            'capacity' => 1,
        ];
    }
}
