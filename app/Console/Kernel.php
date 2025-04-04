<?php

namespace App\Console;

use App\Jobs\BanBotsFromBotList;
use App\Jobs\GetNextMcc;
use App\Jobs\RestartBotJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->job(RestartBotJob::class)->everySixHours();
        $schedule->job(BanBotsFromBotList::class)->everyThirtyMinutes();

        $schedule->job(GetNextMcc::class)->dailyAt('10:00');

        $schedule->command('telescope:prune --hours=72')->daily();
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
