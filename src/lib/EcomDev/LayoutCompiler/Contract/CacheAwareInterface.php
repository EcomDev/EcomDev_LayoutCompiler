<?php

use EcomDev_LayoutCompiler_Contract_CacheInterface as CacheInterface;

/**
 * Cache aware interface
 *
 */
interface EcomDev_LayoutCompiler_Contract_CacheAwareInterface
{
    /**
     * Sets an instance of cache interface
     * 
     * @param CacheInterface $cache
     * @return $this
     */
    public function setCache(CacheInterface $cache);

    /**
     * Returns an instance of cache interface
     * 
     * @return CacheInterface
     */
    public function getCache();
}
