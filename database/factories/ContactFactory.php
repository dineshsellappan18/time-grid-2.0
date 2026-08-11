<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Contact;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'firstname'      => str_pad($this->faker->firstName, 3, '_'),
            'lastname'       => str_pad($this->faker->lastName, 3, '_'),
            'nin'            => $this->faker->numberBetween(25000000, 50000000),
            'email'          => $this->faker->unique()->safeEmail,
            'birthdate'      => Carbon::now()->subYears(30),
            'mobile'         => null,
            'mobile_country' => null,
            'gender'         => $this->faker->randomElement(['M', 'F']),
            'occupation'     => $this->faker->title,
            'martial_status' => null,
            'postal_address' => $this->faker->address,
        ];
    }
}
