<?php

namespace App\Domain\Repository;

use Illuminate\Cache\CacheManager;
use App\Enums\CacheDuration;

abstract class AbstractRepository
{
    public function __construct(
        protected CacheManager $cache
    ) {
    }

    protected function buildCacheKey(string $suffix): string
    {
        return $this->getCachePrefix().'-'.$suffix;
    }

    abstract protected function getCachePrefix(): string;

    protected function rememberCache(string $key, CacheDuration $duration, \Closure $callback): mixed
    {
        return $this->cache->remember($key, $duration->value, $callback);
    }

    protected function clearCache(string $key): void
    {
        $this->cache->forget($key);
    }
}