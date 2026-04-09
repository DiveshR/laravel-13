<?php

namespace App\Services;

use Closure;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class SearchCacheService
{
    private const USERS_VERSION_KEY = 'search:ver:users';
    private const PRODUCTS_VERSION_KEY = 'search:ver:products';

    public function enabled(): bool
    {
        return (bool) config('search.cache.enabled', true);
    }

    public function shouldCacheQuery(string $query): bool
    {
        $min = (int) config('search.cache.min_query_length', 2);

        return mb_strlen(trim($query)) >= $min;
    }

    public function usersKey(string $query, int $page, int $perPage): string
    {
        $version = $this->usersVersion();
        $hash = md5($query.'|p='.$page.'|pp='.$perPage);

        return "search:users:v{$version}:{$hash}";
    }

    public function productsKey(string $query, int $page, int $perPage): string
    {
        $version = $this->productsVersion();
        $hash = md5($query.'|p='.$page.'|pp='.$perPage);

        return "search:products:v{$version}:{$hash}";
    }

    public function combinedKey(string $query): string
    {
        $usersVersion = $this->usersVersion();
        $productsVersion = $this->productsVersion();
        $hash = md5($query);

        return "search:combined:u{$usersVersion}:p{$productsVersion}:{$hash}";
    }

    public function adminUsersRowsKey(string $query, int $page, int $perPage): string
    {
        $version = $this->usersVersion();
        $hash = md5($query.'|p='.$page.'|pp='.$perPage);

        return "admin:users:rows:v{$version}:{$hash}";
    }

    public function adminProductsRowsKey(string $query, int $page, int $perPage): string
    {
        $version = $this->productsVersion();
        $hash = md5($query.'|p='.$page.'|pp='.$perPage);

        return "admin:products:rows:v{$version}:{$hash}";
    }

    /**
     * Cache results with:
     * - jittered TTL (avoids synchronized expiry)
     * - optional lock (prevents stampede)
     *
     * @template T
     *
     * @param  Closure():T  $callback
     * @return array{value:T, hit:bool}
     */
    public function remember(string $key, array $tags, Closure $callback): array
    {
        $cache = $this->cache();

        $cached = $this->get($cache, $tags, $key);
        if ($cached !== null) {
            return ['value' => $cached, 'hit' => true];
        }

        $ttlSeconds = $this->ttlSecondsWithJitter();
        $lockKey = "lock:{$key}";
        $lockSeconds = max(1, (int) config('search.cache.lock_seconds', 10));
        $lockWaitSeconds = max(0, (int) config('search.cache.lock_wait_seconds', 2));

        try {
            $result = $cache
                ->lock($lockKey, $lockSeconds)
                ->block($lockWaitSeconds, function () use ($cache, $tags, $key, $ttlSeconds, $callback) {
                    $cachedInsideLock = $this->get($cache, $tags, $key);
                    if ($cachedInsideLock !== null) {
                        return ['value' => $cachedInsideLock, 'hit' => true];
                    }

                    $computed = $callback();
                    $this->put($cache, $tags, $key, $computed, $ttlSeconds);

                    return ['value' => $computed, 'hit' => false];
                });

            return is_array($result) && array_key_exists('value', $result)
                ? $result
                : ['value' => $result, 'hit' => false];
        } catch (\Throwable) {
            $computed = $callback();
            $this->put($cache, $tags, $key, $computed, $ttlSeconds);

            return ['value' => $computed, 'hit' => false];
        }
    }

    public function bumpUsersVersion(): void
    {
        $this->incrementVersion(self::USERS_VERSION_KEY);
        $this->flushTagsIfSupported(['search', 'search:users', 'search:combined']);
    }

    public function bumpProductsVersion(): void
    {
        $this->incrementVersion(self::PRODUCTS_VERSION_KEY);
        $this->flushTagsIfSupported(['search', 'search:products', 'search:combined']);
    }

    private function usersVersion(): int
    {
        return $this->version(self::USERS_VERSION_KEY);
    }

    private function productsVersion(): int
    {
        return $this->version(self::PRODUCTS_VERSION_KEY);
    }

    private function version(string $key): int
    {
        $cache = $this->cache();
        $cache->add($key, 1);
        $value = $cache->get($key, 1);

        return is_numeric($value) ? (int) $value : 1;
    }

    private function incrementVersion(string $key): void
    {
        $cache = $this->cache();

        try {
            $cache->increment($key);
        } catch (\Throwable) {
            $value = $this->version($key) + 1;
            $cache->forever($key, $value);
        }
    }

    private function ttlSecondsWithJitter(): int
    {
        $base = max(1, (int) config('search.cache.ttl_seconds', 60));
        $jitter = max(0, (int) config('search.cache.ttl_jitter_seconds', 10));

        return $base + ($jitter > 0 ? random_int(0, $jitter) : 0);
    }

    private function cache(): CacheRepository
    {
        $store = config('search.cache.store');

        return $store ? Cache::store((string) $store) : Cache::store();
    }

    private function tagged(CacheRepository $cache, array $tags): CacheRepository
    {
        if ($tags === []) {
            return $cache;
        }

        try {
            return $cache->tags($tags);
        } catch (\Throwable) {
            return $cache;
        }
    }

    private function get(CacheRepository $cache, array $tags, string $key): mixed
    {
        return $this->tagged($cache, $tags)->get($key);
    }

    private function put(CacheRepository $cache, array $tags, string $key, mixed $value, int $ttlSeconds): void
    {
        $this->tagged($cache, $tags)->put($key, $value, $ttlSeconds);
    }

    private function flushTagsIfSupported(array $tags): void
    {
        if ($tags === []) {
            return;
        }

        $cache = $this->cache();

        try {
            $cache->tags($tags)->flush();
        } catch (\Throwable) {
            // Tags may not be supported by non-Redis stores; versions still invalidate.
        }
    }
}
