<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.AutoPost.{id}', function ($user, $id) {
    return Gate::allows("moderate");
});

Broadcast::channel('App.Models.Command.{id}', function ($user, $id) {
    return Gate::allows("moderate");
});

Broadcast::channel('App.MakeNoise', function ($user) {
    return Gate::allows("moderate");
});
