<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Permission;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $name = $this->faker->word;

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $this->faker->sentence,
        ];
    }
}
