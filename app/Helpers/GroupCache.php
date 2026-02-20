<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class GroupCache
{
    public static function remember(string $group, string $key, int $ttl, callable $callback)
    {
        self::registerCacheKey($group, $key);
        return Cache::remember($key, $ttl, $callback);
    }

    public static function rememberForever(string $group, string $key, callable $callback)
    {
        self::registerCacheKey($group, $key);
        return Cache::rememberForever($key, $callback);
    }

    public static function registerCacheKey(string $group, string $key): void
    {
        $registryKey = self::getRegistryKey($group);
        $keys = Cache::get($registryKey, []);

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever($registryKey, $keys);
        }
    }

    public static function forgetGroup(string $group): void
    {
        $registryKey = self::getRegistryKey($group);
        $keys = Cache::get($registryKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($registryKey);
    }

    protected static function getRegistryKey(string $group): string
    {
        return "cache_registry:{$group}";
    }

    // potentially to use but not in use yet
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
