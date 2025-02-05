<?php

namespace App\Console\Commands;

use App\Jobs\GetNextMcc as JobsGetNextMcc;
use Illuminate\Console\Command;

class GetNextMcc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcc:get-next';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get next mcc and put in database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        JobsGetNextMcc::dispatchSync();
    }
}
