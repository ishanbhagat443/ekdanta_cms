<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('gsm:publish-scheduled-posts')->everyMinute();

        // Nightly DB backup (storage/app/backups, 14-day retention).
        $schedule->command('gsm:backup')->dailyAt('02:30');

        // Queued mail that failed 3 times gets flushed weekly.
        $schedule->command('queue:prune-failed', ['--hours' => 168])->weekly();
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
