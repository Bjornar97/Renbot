<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

it('has term page', function () {
    actingAs(User::factory()->moderator()->create());

    $response = $this->get('/moderators/blocked-terms');

    $response->assertStatus(200);
});
