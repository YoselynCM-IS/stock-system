<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\ActsVencidos',
        'App\Console\Commands\ActsPendProx',
        'App\Console\Commands\BajaCodeCommand',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('actividades:vencidos')->everyMinute();
        $schedule->command('actividades:pendprox')->hourly();
        $schedule->command('actividades:recordatorio')->everyTenMinutes();
        $schedule->command('codes:baja')->everyMinute();
        // ->twiceDaily(7, 19);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
