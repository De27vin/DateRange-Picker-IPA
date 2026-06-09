<?php

use App\Helpers\GroupCache;
use Illuminate\Support\Facades\DB;
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
    $accountId = (int) $accountId;
    $cacheKey = "user_realtime_account_access_{$user->user_id}_{$accountId}";

    return GroupCache::remember('users_roles_permissions', $cacheKey, 24 * 60 * 60, function () use ($user, $accountId) {
        $hasSiteRole = DB::table('users_roles')
            ->join('roles', 'users_roles.ur_role_id', '=', 'roles.role_id')
            ->where('users_roles.ur_user_id', $user->user_id)
            ->where('roles.role_type', 'site')
            ->whereNull('users_roles.ur_account_id')
            ->exists();

        if ($hasSiteRole) {
            return true;
        }

        return DB::table('users_roles')
            ->where('ur_user_id', $user->user_id)
            ->where('ur_account_id', $accountId)
            ->exists();
    });
});
