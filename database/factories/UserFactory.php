<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->firstName,
            'name'     => $this->faker->firstName,
            'email'    => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
        ];
    }
}
