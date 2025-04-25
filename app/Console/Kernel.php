<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // This task will delete all sessions daily
        $schedule->command('app:exp-session')->daily();

        // Example of other schedules:
        // $schedule->command('your:custom-command')->everyMinute();
        // $schedule->call([YourClass::class, 'methodName'])->hourly();
    }

    /**
     * The Artisan commands provided by your application
     * 
     * @var array
     */
    protected $commands = [
        'App\Console\Command\ExpSession'
    ];
}
