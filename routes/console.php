<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
| Schedule definitions are also registered here as of Laravel 11.
|
*/

Schedule::command('root:report')->dailyAt(config('root.report.time'));
Schedule::command('business:report')->dailyAt('21:00');
Schedule::command('business:vacancies')->weekly()->sundays()->at('00:00');
Schedule::command('ical:sync')->twiceDaily(0, 12);
