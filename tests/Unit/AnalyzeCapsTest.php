<?php

use App\Jobs\Analysis\AnalyzeCapsJob;
use GhostZero\Tmi\Channel;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use GhostZero\Tmi\Tags;

it('is punishable caps', function (string $message) {
    $channel = new Channel("rendogtv");
    $tags = new Tags([]);
    $messageEvent = new MessageEvent(
        $channel,
        $tags,
        "testuser",
        $message,
        false
    );

    $capsAnalyzer = new AnalyzeCapsJob($messageEvent);

    expect($capsAnalyzer->isPunishable())->toBeTrue();
})->with([
    'I AM CAPS',
    'this is not that many caps, but has a long caps word GUTENTAG',
    "GUTEN Morgen",
    'GUTEN',
]);

it("is not punishable caps", function (string $message) {
    $channel = new Channel("rendogtv");
    $tags = new Tags([]);
    $messageEvent = new MessageEvent(
        $channel,
        $tags,
        "testuser",
        $message,
        false
    );

    $capsAnalyzer = new AnalyzeCapsJob($messageEvent);

    expect($capsAnalyzer->isPunishable())->toBeFalse();
})->with([
    'this is not caps',
    'This Has One Caps Per Word',
    'OMG That is an abbrievation',
    'FOAF is an acronym',
]);


test("emotes doesnt count", function () {
    $channel = new Channel("rendogtv");

    $tags = new Tags([
        'emotes' => '425618:0-2,4-6/425618:8-14',
    ]);

    $messageEvent = new MessageEvent(
        $channel,
        $tags,
        "testuser",
        "LUL LUL LULULUL",
        false
    );

    $capsAnalyzer = new AnalyzeCapsJob($messageEvent);

    expect($capsAnalyzer->isPunishable())->toBeFalse();
});
