<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

test("moderator can load commands index", function () {
    actingAs(User::factory()->moderator()->create());

    get(route("commands.index"))->assertStatus(200);
});

test("viewer cannot load commands index", function () {
    actingAs(User::factory()->viewer()->create());

    get(route("commands.index"))->assertForbidden();
});
