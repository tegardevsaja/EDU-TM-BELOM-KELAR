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
        // Auto-graduate students daily at 2:00 AM
        $schedule->command('students:auto-graduate')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->appendOutputTo(storage_path('logs/auto-graduation.log'));

        // Optional: Run dry-run weekly for monitoring
        $schedule->command('students:auto-graduate --dry-run')
                 ->weeklyOn(1, '08:00') // Every Monday at 8:00 AM
                 ->appendOutputTo(storage_path('logs/auto-graduation-preview.log'));
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
