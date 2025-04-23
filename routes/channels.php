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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('tcp_status_channel', function () {
//     return true; // 允許所有使用者訂閱
// });
Broadcast::channel('tcp_status_channel', function ($user) {
    return true; // 允許所有使用者訂閱
});