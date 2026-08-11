<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('today', 'today +7 days')->format('Y-m-d');

        return [
            'business_id' => BusinessFactory::new()->create()->id,
            'service_id'  => ServiceFactory::new()->create()->id,
            'date'        => date('Y-m-d', strtotime($date)),
            'start_at'    => date('Y-m-d 00:00:00', strtotime($date)),
            'finish_at'   => date('Y-m-d 23:00:00', strtotime($date)),
            'capacity'    => 1,
        ];
    }
}
