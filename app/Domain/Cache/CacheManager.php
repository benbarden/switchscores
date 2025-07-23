<?php

namespace App\Domain\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Closure;

class CacheManager
{
    /**
     * Get or store a value in the cache.
     *
     * @param string $key
     * @param int $ttlInSeconds
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $ttlInSeconds, Closure $callback)
    {
        try {
            return Cache::store('redis')->remember($key, $ttlInSeconds, $callback);
        } catch (\Exception $e) {
            Log::warning("Redis cache failed, falling back: " . $e->getMessage());
            return Cache::store('array')->remember($key, $ttlInSeconds, $callback);
        }
    }

    /**
     * Remove a key from cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        try {
            return Cache::store('redis')->forget($key);
        } catch (\Exception $e) {
            Log::warning("Redis cache failed, falling back: " . $e->getMessage());
            return Cache::store('array')->forget($key);
        }
    }
}