<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Timegridio\Concierge\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->sentence(3),
            'slug'        => Str::slug($this->faker->name),
            'description' => $this->faker->paragraph,
            'strategy'    => 'dateslot',
        ];
    }
}
