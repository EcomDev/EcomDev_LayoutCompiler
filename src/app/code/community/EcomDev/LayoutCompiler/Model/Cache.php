<?php

/**
 * Cache instance for managing layout compilers
 *
 */
class EcomDev_LayoutCompiler_Model_Cache
    implements EcomDev_LayoutCompiler_Contract_CacheInterface
{
    /**
     * Application model for cache storage
     *
     * @var Mage_Core_Model_App
     */
    private $app;

    /**
     * Loads data from cache or returns null
     *
     * @param $cacheKey
     * @return string|array|object|int|null
     */
    public function load($cacheKey)
    {
        $data = $this->getApp()->loadCache($cacheKey);

        if ($data === false) {
            return $data;
        }

        return json_decode($data, true);
    }

    /**
     * Saves a data to cache
     *
     * @param string $cacheKey
     * @param string|array|object|int $data
     * @param bool|int $cacheLifetime
     * @return $this
     */
    public function save($cacheKey, $data, $cacheLifetime = false)
    {
        $this->getApp()->saveCache(
            json_encode($data),
            $cacheKey,
            array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG),
            $cacheLifetime
        );

        return $this;
    }

    /**
     * Sets application for testing
     *
     * @param Mage_Core_Model_App $app
     * @return $this
     */
    public function setApp(Mage_Core_Model_App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Returns application instance used for cache
     *
     * @return Mage_Core_Model_App
     */
    public function getApp()
    {
        if ($this->app === null) {
            $this->setApp(Mage::app());
        }

        return $this->app;
    }
}
