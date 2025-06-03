<?php

namespace App\Console\Commands;

use App\Services\TwitchWebhookService;
use Illuminate\Console\Command;
use romanzipp\Twitch\Enums\EventSubType;

class SubscribeToTwitchWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:subscribe-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to twitch webhooks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $webhookNames = [EventSubType::STREAM_ONLINE, EventSubType::STREAM_OFFLINE, 'channel.chat.message'];

        $webhookService = TwitchWebhookService::connect();

        foreach ($webhookNames as $name) {
            try {
                $webhookService->subscribeToWebhook($name);
            } catch (\Throwable $th) {
                if ($th->getCode() === 409) {
                    $this->info('Webhook already subscribed: '.$name);

                    continue;
                }

                $this->error('Failed to subscribe to webhook: '.$name.' - '.$th->getMessage());
            }
        }

        $this->info('Successfully subscribed to twitch webhooks');

        return Command::SUCCESS;
    }
}
