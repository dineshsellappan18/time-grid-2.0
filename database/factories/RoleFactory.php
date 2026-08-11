<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = $this->faker->word;

        return [
            'name'        => $this->faker->word,
            'slug'        => Str::slug($name),
            'description' => $this->faker->sentence,
        ];
    }
}
