<?php

/**
 * Simplified interface for operations with cache storage
 * 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_CacheInterface
{
    /**
     * Loads data from cache or returns null
     * 
     * @param $cacheKey
     * @return string|array|object|int|null
     */
    public function load($cacheKey);

    /**
     * Saves a data to cache
     * 
     * @param string $cacheKey
     * @param string|array|object|int $data
     * @param bool|int $cacheLifetime
     * @return mixed
     */
    public function save($cacheKey, $data, $cacheLifetime = false);
}
