#!/usr/bin/env php
<?php

/**
 * One-off helper to (re)generate WO-004 golden JSON fixtures.
 * Usage: TEST_DB_*=... php tests/characterization/bin/generate-golden-fixtures.php
 */

require __DIR__.'/../../../vendor/autoload.php';

$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Illuminate\Support\Facades\DB::beginTransaction();

try {

Carbon\Carbon::setTestNow(Carbon\Carbon::parse('2024-06-15 15:00:00', 'UTC'));
$tz = 'America/Argentina/Buenos_Aires';
$vacancyDate = '2024-06-17';
$dstDate = '2008-03-16';
$fixturesDir = __DIR__.'/../fixtures';

if (!is_dir($fixturesDir)) {
    mkdir($fixturesDir, 0755, true);
}

$owner = factory(App\Models\User::class)->create([
    'username' => 'char_owner_wo004',
    'email'    => 'char-owner-wo004@example.test',
]);

$issuer = factory(App\Models\User::class)->create([
    'username' => 'char_issuer_wo004',
    'email'    => 'char-issuer-wo004@example.test',
]);

$business = factory(Timegridio\Concierge\Models\Business::class)->create([
    'name'     => 'Characterization Venue BA WO004',
    'timezone' => $tz,
    'strategy' => 'timeslot',
    'plan'     => 'free',
]);
$business->owners()->save($owner);
$business->pref('timeslot_step', 15);
$business->pref('availability_future_days', 7);
$business->pref('appointment_take_today', false);
$business->pref('appointment_code_length', 4);

$contact = factory(Timegridio\Concierge\Models\Contact::class)->create([
    'firstname' => 'Char',
    'lastname'  => 'Guest',
    'email'     => 'char-guest-wo004@example.test',
]);
$contact->user()->associate($issuer);
$business->contacts()->save($contact);

$services = [];
foreach ([15, 30, 60, 120] as $duration) {
    $service = factory(Timegridio\Concierge\Models\Service::class)->make([
        'name'     => "Char Service {$duration}m",
        'duration' => $duration,
    ]);
    $service->business()->associate($business);
    $service->save();
    $services[$duration] = $service;
}

$makeVacancy = function ($date, $service) use ($business, $tz) {
    $vacancy = factory(Timegridio\Concierge\Models\Vacancy::class)->make([
        'date'      => $date,
        'start_at'  => Carbon\Carbon::parse("{$date} 09:00:00", $tz)->timezone('UTC'),
        'finish_at' => Carbon\Carbon::parse("{$date} 17:00:00", $tz)->timezone('UTC'),
        'capacity'  => 1,
    ]);
    $vacancy->business()->associate($business);
    $vacancy->service()->associate($service);
    $vacancy->save();

    return $vacancy;
};

$vacancy = $makeVacancy($vacancyDate, $services[30]);
$dstVacancy = $makeVacancy($dstDate, $services[30]);

$seedAppointment = factory(Timegridio\Concierge\Models\Appointment::class)->make([
    'status'   => Timegridio\Concierge\Models\Appointment::STATUS_CONFIRMED,
    'start_at' => Carbon\Carbon::parse("{$vacancyDate} 10:00:00", $tz)->timezone('UTC'),
    'duration' => 30,
    'comments' => 'Characterization seed appointment',
]);
$seedAppointment->business()->associate($business);
$seedAppointment->contact()->associate($contact);
$seedAppointment->service()->associate($services[30]);
$seedAppointment->vacancy()->associate($vacancy);
$seedAppointment->issuer()->associate($owner);
$seedAppointment->save();

$availability = new App\TG\Availability\AvailabilityService();
$availability->timezone($tz);

$write = function ($name, $data) use ($fixturesDir) {
    file_put_contents(
        $fixturesDir.'/'.$name.'.json',
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );
    echo "Wrote {$name}.json\n";
};

foreach ([15, 30] as $step) {
    $business->pref('timeslot_step', $step);
    foreach ([15, 30, 60, 120] as $duration) {
        $vacancy->date = $vacancyDate;
        $vacancy->start_at = Carbon\Carbon::parse("{$vacancyDate} 09:00:00", $tz)->timezone('UTC');
        $vacancy->finish_at = Carbon\Carbon::parse("{$vacancyDate} 17:00:00", $tz)->timezone('UTC');
        $vacancy->service()->associate($services[$duration]);
        $vacancy->save();

        $times = $availability->getTimes($business->fresh(), $services[$duration], Carbon\Carbon::parse($vacancyDate));
        $write("availability-slots-{$duration}-step{$step}", $times);
    }
}

$business->pref('timeslot_step', 15);
$timesDst = $availability->getTimes($business->fresh(), $services[30], Carbon\Carbon::parse($dstDate));
$write('availability-slots-dst-2008-03-16', $timesDst);

$vacancy->service()->associate($services[30]);
$vacancy->save();
$timesApi = $availability->getTimes($business->fresh(), $services[30], Carbon\Carbon::parse($vacancyDate));
$write('availability-api-times-30', $timesApi);

echo "Done.\n";
    Illuminate\Support\Facades\DB::rollBack();
} catch (Exception $e) {
    Illuminate\Support\Facades\DB::rollBack();
    throw $e;
}
