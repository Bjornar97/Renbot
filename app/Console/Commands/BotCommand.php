<?php

namespace App\Console\Commands;

use App\Jobs\CommandJob;
use App\Jobs\DeleteTwitchMessageJob;
use App\Models\Command as ModelsCommand;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class BotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the bot';

    private Client $client;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oauthToken = config("services.twitch.oauth_token");

        $this->client = new Client(new ClientOptions([
            'options' => ['debug' => true],
            'connection' => [
                'secure' => true,
                'reconnect' => true,
                'rejoin' => true,
            ],
            'identity' => [
                'username' => 'RenTheBot',
                'password' => $oauthToken,
            ],
            'channels' => ['rendogtv'],
        ]));

        $this->client->on(MessageEvent::class, function (MessageEvent $message) {
            $this->onMessage($message);
        });

        $this->client->connect();

        return Command::SUCCESS;
    }

    public function onMessage(MessageEvent $message)
    {
        if ($message->self) return;

        $command = $this->getCommandFromMessage($message->message);

        $this->info("Command: $command?->command");

        if (!$command) {
            return;
        }

        if (!$this->isAuthorized($command, $message)) {
            DeleteTwitchMessageJob::dispatchSync($message->tags['id']);
            return;
        }

        $response = "";

        if ($this->targetUsername) {
            $response .= "@{$this->targetUsername} ";
        }

        $response .= $this->command->response;

        CommandJob::dispatchSync($command, $this->getTargetUsernameFromMessage($message->message), $message);
    }

    public function isAuthorized(ModelsCommand $command, MessageEvent $message)
    {
        $isModerator = (bool) $message->tags['mod'];

        if ($isModerator) {
            return true;
        }

        if ($command->everyone_can_use) {
            return true;
        }

        $isSubscriber = (bool) $message->tags['subscriber'];

        if ($isSubscriber && $command->subscriber_can_use) {
            return true;
        }

        return false;
    }

    public function getCommandFromMessage(string $message): ModelsCommand|null
    {
        $message = trim($message);

        $words = explode(" ", $message);

        $command = $words[0] ?? null;

        if (!str_contains($command, "!")) {
            return null;
        }

        $command = str_replace("!", "", $command);

        $command = ModelsCommand::active()->where('command', $command)->first();

        return $command;
    }

    public function getTargetUsernameFromMessage(string $message): string | null
    {
        $message = trim($message);

        $words = explode(" ", $message);

        $target = $words[1] ?? null;

        if (!$target) {
            return null;
        }

        $target = str_replace("@", "", $target);

        return $target;
    }
}
