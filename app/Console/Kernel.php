<?php

namespace App\Console;

use App\Jobs\AppointmentReminderJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule appointment reminders to run hourly
        $schedule->job(new AppointmentReminderJob)
            ->hourly()
            ->description('Send appointment reminders (24h and 2h before appointments)')
            ->withoutOverlapping();
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
