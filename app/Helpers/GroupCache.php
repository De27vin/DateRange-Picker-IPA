<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GroupCache
{
    /**
     * Per-request cache of group versions to avoid repeated cache reads
     */
    protected static $versionCache = [];

    public static function remember(string $group, string $key, int $ttl, callable $callback)
    {
        $prefixedKey = self::getPrefixedKey($group, $key);
        return Cache::remember($prefixedKey, $ttl, $callback);
    }

    public static function rememberForever(string $group, string $key, callable $callback)
    {
        $prefixedKey = self::getPrefixedKey($group, $key);
        // Use 7-day TTL instead of forever to prevent unbounded storage leak from orphaned entries
        return Cache::remember($prefixedKey, 604800, $callback);
    }

    public static function forgetGroup(string $group): void
    {
        Cache::forget("group_version:{$group}");
        unset(self::$versionCache[$group]);
    }

    protected static function getPrefixedKey(string $group, string $key): string
    {
        // Memoize version per request to avoid repeated cache reads
        if (!isset(self::$versionCache[$group])) {
            self::$versionCache[$group] = Cache::remember(
                "group_version:{$group}",
                604800, // 7 days - same as rememberForever TTL
                fn() => Str::uuid()->toString()
            );
        }

        $version = self::$versionCache[$group];
        return "group:{$group}:{$version}:{$key}";
    }

    protected static function makeCacheKey(...$params): string
    {
        $serializedParams = array_map(function ($param) {
            if (is_object($param) && method_exists($param, 'toSql')) {
                return [
                    'sql' => $param->toSql(),
                    'bindings' => $param->getBindings(),
                ];
            }
            return $param;
        }, $params);

        return md5(json_encode($serializedParams));
    }
}
