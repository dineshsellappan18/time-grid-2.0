<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'business_id' => BusinessFactory::new()->create()->id,
            'contact_id'  => ContactFactory::new()->create()->id,
            'service_id'  => ServiceFactory::new()->create()->id,
            'vacancy_id'  => VacancyFactory::new()->create()->id,
            'status'      => $this->faker->randomElement(['R', 'C', 'A', 'S']),
            'start_at'    => Carbon::parse(date('Y-m-d 08:00:00', strtotime('today +2 days'))),
            'duration'    => $this->faker->randomElement([15, 30, 60, 120]),
            'comments'    => $this->faker->sentence,
        ];
    }
}
