<?php

use App\Models\Quote;
use App\Models\Streamday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('viewer cannot create punishable or special commands', function () {
    actingAs(User::factory()->viewer()->create());

    post(route('punishable-commands.store'))->assertForbidden();
    post(route('special-commands.store'))->assertForbidden();
});

test('viewer cannot delete quotes', function () {
    $quote = Quote::factory()->create();

    actingAs(User::factory()->viewer()->create());

    delete(route('quotes.destroy', $quote))->assertForbidden();

    expect($quote->fresh())->not->toBeNull();
});

test('viewer cannot edit or delete streamdays', function () {
    $streamday = Streamday::query()->create([
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
    ]);

    actingAs(User::factory()->viewer()->create());

    $this->get(route('streamdays.edit', $streamday))->assertForbidden();
    delete(route('streamdays.destroy', $streamday))->assertForbidden();

    expect($streamday->fresh())->not->toBeNull();
});
