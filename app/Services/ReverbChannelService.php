<?php

namespace App\Services;

use App\Helpers\GroupCache;
use Illuminate\Support\Facades\DB;

class ReverbChannelService
{
    private const CACHE_GROUP = 'users_roles_permissions';
    private const CACHE_TTL = 24 * 60 * 60;

    public function userCanAccessAccount($user, int $accountId): bool
    {
        $cacheKey = "user_realtime_account_access_{$user->user_id}_{$accountId}";

        return GroupCache::remember(
            self::CACHE_GROUP,
            $cacheKey,
            self::CACHE_TTL,
            function () use ($user, $accountId) {
                return $this->checkUserHasAccountAccess($user, $accountId);
            }
        );
    }

    /**
     * Invalidate all cached user permissions
     * todo: Call this when roles change
     */
    public static function invalidateCache(): void
    {
        GroupCache::forgetGroup(self::CACHE_GROUP);
    }

    private function userHasSuperuserSiteRole($user): bool
    {
        $cacheKey = "user_site_role_{$user->user_id}";

        return GroupCache::remember(
            self::CACHE_GROUP,
            $cacheKey,
            self::CACHE_TTL,
            function () use ($user) {
                return DB::table('users_roles')
                    ->join('roles', 'users_roles.ur_role_id', '=', 'roles.role_id')
                    ->where('users_roles.ur_user_id', $user->user_id)
                    ->where('roles.role_type', 'site')
                    ->exists();
            }
        );
    }

    private function checkUserHasAccountAccess($user, int $accountId): bool
    {
        // Site users have access to all accounts
        $hasSiteRole = DB::table('users_roles')
            ->join('roles', 'users_roles.ur_role_id', '=', 'roles.role_id')
            ->where('users_roles.ur_user_id', $user->user_id)
            ->where('roles.role_type', 'site')
            ->whereNull('users_roles.ur_account_id')
            ->exists();

        if ($hasSiteRole) {
            return true;
        }

        // Check if user has any role for this specific account
        return DB::table('users_roles')
            ->where('ur_user_id', $user->user_id)
            ->where('ur_account_id', $accountId)
            ->exists();
    }
}
