<?php

use EcomDev_LayoutCompiler_Contract_CacheInterface as CacheInterface;

trait EcomDev_LayoutCompiler_CacheAwareTrait
{
    private $cache;

    /**
     * Sets an instance of cache interface
     *
     * @param CacheInterface $cache
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Returns an instance of cache interface
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
