<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\AutopublishBusinessVacancies::class,
        \App\Console\Commands\SendRootReport::class,
        \App\Console\Commands\SendBusinessReport::class,
        \App\Console\Commands\SyncICal::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('root:report')->dailyAt(config('root.report.time'));
        $schedule->command('business:report')->dailyAt('21:00');
        $schedule->command('business:vacancies')->weekly()->sundays()->at('00:00');
        $schedule->command('ical:sync')->twiceDaily(0, 12);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
