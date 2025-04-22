<?php

use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Models\Message;

it('is punishable caps', function (string $messageContent) {
    $fragments = [
        ["type" => "text", "text" => $messageContent, "cheermote" => null, "emote" => null, "mention" => null]
    ];

    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => $messageContent,
        'fragments' => $fragments,
    ]);

    $capsAnalyzer = new AnalyzeCapsJob($message);

    expect($capsAnalyzer->isPunishable())->toBeTrue();
})->with([
    'I AM CAPS',
    'this is not that many caps, but has a long caps word GUTENTAG',
    'GUTEN Morgen',
    'GUTEN',
    'its giving "BAM ME IN THE"',
    'this is mostly lower case BUT HAS SOME caps words',
]);

it('is not punishable caps', function (string $messageContent) {
    $fragments = [
        ["type" => "text", "text" => $messageContent, "cheermote" => null, "emote" => null, "mention" => null]
    ];

    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => $messageContent,
        'fragments' => $fragments,
    ]);

    $capsAnalyzer = new AnalyzeCapsJob($message);

    expect($capsAnalyzer->isPunishable())->toBeFalse();
})->with([
    'this is not caps',
    'This Has One Caps Per Word',
    'OMG That is an acronym',
    'FOAF is an acronym',
    'This is a normal message ACRO 1 and ACRO 2 and then ACRO 3 should be fine',
]);

test('emotes doesnt count', function () {
    $message = Message::create([
        'twitch_user_id' => 1,
        'username' => 'testuser',
        'message' => 'LUL LUL LULULUL',
        'fragments' => [
            ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "emotesv2_c4e36990dfa548e9baa10ac7084df6c8", "emote_set_id" => "16961", "owner_id" => "30600786", "format" => ["static"]], "mention" => null],
            ["type" => "text", "text" => null, "cheermote" => null, "emote" => null, "mention" => null],
            ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "emotesv2_c4e36990dfa548e9baa10ac7084df6c8", "emote_set_id" => "16961", "owner_id" => "30600786", "format" => ["static"]], "mention" => null],
            ["type" => "text", "text" => null, "cheermote" => null, "emote" => null, "mention" => null],
            ["type" => "emote", "text" => "LUL", "cheermote" => null, "emote" => ["id" => "emotesv2_c4e36990dfa548e9baa10ac7084df6c8", "emote_set_id" => "16961", "owner_id" => "30600786", "format" => ["static"]], "mention" => null]
        ],
    ]);

    $capsAnalyzer = new AnalyzeCapsJob($message);

    expect($capsAnalyzer->isPunishable())->toBeFalse();
});
