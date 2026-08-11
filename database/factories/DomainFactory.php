<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Timegridio\Concierge\Models\Domain;
use App\Models\User;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'slug'     => Str::slug($this->faker->name),
            'owner_id' => UserFactory::new()->create()->id,
        ];
    }
}
