<?php

use EcomDev_Layout_Contract_CacheInterface as CacheInterface;

/**
 * Cache aware interface
 *
 */
interface EcomDev_Layout_Contract_Cache_AwareInterface
{
    /**
     * Sets an instance of cache interface
     * 
     * @param CacheInterface $interface
     * @return $this
     */
    public function setCache(CacheInterface $interface);

    /**
     * Returns an instance of cache interface
     * 
     * @return CacheInterface
     */
    public function getCache();
}
