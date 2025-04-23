<?php

namespace App\Console;

use App\Jobs\AutoPostCheckJob;
use App\Jobs\BanBotsFromBotList;
use App\Jobs\GetNextMcc;
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
        $schedule->job(AutoPostCheckJob::class)->everyTenSeconds();

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
