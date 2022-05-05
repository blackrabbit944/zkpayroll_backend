<?php

use Illuminate\Support\Facades\Broadcast;

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

Broadcast::channel('User.{user_id}', function ($user, $user_id) {
    return (int) $user->user_id === (int) $user_id;
});

Broadcast::channel('Chat.{club_id}', function ($user) {
    return $user;
});