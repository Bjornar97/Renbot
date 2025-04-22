<?php

use App\Jobs\Analysis\AnalyzeEmotesJob;
use App\Models\Message;

it('is punishable for too many emotes', function () {
    $fragments = [
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
    ];

    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => 'LUL LUL LUL LUL LUL LUL',
        'fragments' => $fragments,
    ]);

    $emoteAnalyzer = new AnalyzeEmotesJob($message);

    expect($emoteAnalyzer->isPunishable())->toBeTrue();
});

it('is not punishable for acceptable emotes count', function () {
    $fragments = [
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
        ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "25"], "mention" => null],
    ];

    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => 'LUL LUL LUL',
        'fragments' => $fragments,
    ]);

    $emoteAnalyzer = new AnalyzeEmotesJob($message);

    expect($emoteAnalyzer->isPunishable())->toBeFalse();
});

it('is not punishable when no emotes are present', function () {
    $fragments = [
        ["type" => "text", "text" => "This is a normal message", "cheermote" => null, "emote" => null, "mention" => null]
    ];

    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => 'This is a normal message',
        'fragments' => $fragments,
    ]);

    $emoteAnalyzer = new AnalyzeEmotesJob($message);

    expect($emoteAnalyzer->isPunishable())->toBeFalse();
});

it('is not punishable when fragments is null', function () {
    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => 'This is a normal message',
        'fragments' => null,
    ]);

    $emoteAnalyzer = new AnalyzeEmotesJob($message);

    expect($emoteAnalyzer->isPunishable())->toBeFalse();
});
