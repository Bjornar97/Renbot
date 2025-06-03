<?php

namespace App\Console\Commands;

use App\Services\TwitchWebhookService;
use Illuminate\Console\Command;

class UnsubscribeToAllTwitchWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:unsubscribe-from-all-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $webhookService = TwitchWebhookService::connect();

        $webhookService->unsubscribeFromAllWebhooks();

        $this->info('Successfully unsubscribed from all Twitch webhooks');
    }
}
