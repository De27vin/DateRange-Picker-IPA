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

// -------------------------------------------------------------------------
// Unified Realtime Channel (New Architecture)
// -------------------------------------------------------------------------

Broadcast::channel('realtime.account.{accountId}', function ($user, $accountId) {
    $channelService = new \App\Services\ReverbChannelService();
    return $channelService->userCanAccessAccount($user, $accountId);
});
