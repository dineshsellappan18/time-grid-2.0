<?php

use App\Factories\AppointmentFactory;
use App\Factories\BusinessFactory;
use App\Factories\CategoryFactory;
use App\Factories\ContactFactory;
use App\Factories\DomainFactory;
use App\Factories\HumanresourceFactory;
use App\Factories\PermissionFactory;
use App\Factories\RoleFactory;
use App\Factories\ServiceFactory;
use App\Factories\ServiceTypeFactory;
use App\Factories\UserFactory;
use App\Factories\VacancyFactory;

$factory->define(App\Models\User::class, function ($faker) {
    return UserFactory::definition($faker);
});

$factory->define(App\Models\Role::class, function ($faker) {
    return RoleFactory::definition($faker);
});

$factory->define('App\Models\Permission', function ($faker) {
    return PermissionFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Contact::class, function ($faker) {
    return ContactFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Business::class, function ($faker) {
    return BusinessFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Category::class, function ($faker) {
    return CategoryFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Humanresource::class, function ($faker) {
    return HumanresourceFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\ServiceType::class, function ($faker) {
    return ServiceTypeFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Service::class, function ($faker) {
    return ServiceFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Vacancy::class, function ($faker) {
    return VacancyFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Appointment::class, function ($faker) {
    return AppointmentFactory::definition($faker);
});

$factory->define(Timegridio\Concierge\Models\Domain::class, function ($faker) {
    return DomainFactory::definition($faker);
});
