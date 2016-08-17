<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_IndexInterface as IndexInterface;
use EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface as UpdateInterface;
use EcomDev_LayoutCompiler_Contract_FactoryAwareInterface as FactoryAwareInterface;
use EcomDev_LayoutCompiler_Contract_CacheAwareInterface as CacheAwareInterface;

class EcomDev_LayoutCompiler_Model_Layout_Update
    extends Mage_Core_Model_Layout_Update
    implements UpdateInterface, FactoryAwareInterface, CacheAwareInterface
{
    const CACHE_KEY_VALIDATION = 'LAYOUT_VALIDITY_%s_%s_%s_%s_%s';

    use EcomDev_LayoutCompiler_LayoutAwareTrait;
    use EcomDev_LayoutCompiler_FactoryAwareTrait;
    use EcomDev_LayoutCompiler_CacheAwareTrait;

    /**
     * List of indexes available
     *
     * @var IndexInterface[]
     */
    private $index = array();

    /**
     * @var Mage_Core_Model_Design_Package
     */
    private $designPackage;

    /**
     * Design parameters holder
     *
     * @var string[]
     */
    private $designParameters;

    /**
     * Returns parameters for index
     *
     * @return $this
     */
    private function getDesignParameters()
    {
        if ($this->designParameters === null) {
            $this->designParameters = array(
                'area' => $this->getDesignPackage()->getArea(),
                'package' => $this->getDesignPackage()->getPackageName(),
                'theme' => $this->getDesignPackage()->getTheme('layout'),
                'store_id' => Mage::app()->getStore()->getId()
            );
        }

        return $this->designParameters;
    }

    /**
     * Returns design package
     *
     * @return Mage_Core_Model_Design_Package
     */
    public function getDesignPackage()
    {
        if ($this->designPackage === null) {
            $this->setDesignPackage();
        }

        return $this->designPackage;
    }

    /**
     * Sets a design package
     *
     * @param null|Mage_Core_Model_Design_Package $package
     * @return $this
     */
    public function setDesignPackage($package = null)
    {
        if ($package === null) {
            $package = Mage::getSingleton('core/design_package');
        }

        $this->designPackage = $package;
        return $this;
    }

    /**
     * Loads runtime updates into the processor
     *
     * In case if there is no sources added to runtime
     * it just skips all the process
     *
     * @return $this
     */
    public function loadRuntime()
    {
        $sources = $this->getSources(self::INDEX_RUNTIME);

        if (empty($sources)) {
            return $this;
        }

        $this->loadIndex(self::INDEX_RUNTIME);
        $index = $this->getIndex(self::INDEX_RUNTIME);
        $index->update($sources);
        $index->save($this->getIndexParams(self::INDEX_RUNTIME));

        $loader  = $this->getLayout()->getLoader();
        $processor = $this->getLayout()->getProcessor();

        foreach ($sources as $source) {
            $loader->loadIntoProcessor(
                $source->getId(), $processor, $index
            );
        }

        return $this;
    }

    /**
     * Loads layout updates from index and periodically revalidates the cache key
     *
     * @param string[]|string $handles
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function load($handles=array())
    {
        if (is_string($handles)) {
            $handles = array($handles);
        } elseif (!is_array($handles)) {
            throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid layout update handle'));
        }

        foreach ($handles as $handle) {
            $this->addHandle($handle);
        }

        // Override handles to retrieve them from pre-configured list
        $handles = $this->getHandles();

        if (empty($handles)) {
            return $this;
        }

        Varien_Profiler::start('layout-compiler::load-updates');
        Varien_Profiler::start('layout-compiler::load-updates::load_index');
        $validIndex = $this->loadIndex();
        $index = $this->getIndex();
        Varien_Profiler::stop('layout-compiler::load-updates::load_index');
        $validationCacheKey = $this->getValidationCacheKey();
        $validCache = $this->getCache()->load($validationCacheKey);

        Varien_Profiler::start('layout-compiler::load-updates::update_index');
        if (!$validIndex || !$validCache) {
            $index->update($this->getSources());
            $index->save($this->getIndexParams());
        }
        Varien_Profiler::stop('layout-compiler::load-updates::update_index');

        Varien_Profiler::start('layout-compiler::load-updates::save_cache');
        if (!$validCache) {
            $this->getCache()->save($validationCacheKey, true, 3600);
        }
        Varien_Profiler::stop('layout-compiler::load-updates::save_cache');

        Varien_Profiler::start('layout-compiler::load-updates::load_items');
        $loader  = $this->getLayout()->getLoader();
        $processor = $this->getLayout()->getProcessor();

        foreach ($handles as $handle) {
            $loader->loadIntoProcessor($handle, $processor, $index);
        }

        Varien_Profiler::stop('layout-compiler::load-updates::load_items');

        Varien_Profiler::stop('layout-compiler::load-updates');
        return $this;
    }

    /**
     * Returns a cache identifier used for cache key validation
     *
     * @return string
     */
    private function getValidationCacheKey()
    {
        $parameters = $this->getDesignParameters();
        return sprintf(
            self::CACHE_KEY_VALIDATION,
            gethostname(),
            $parameters['area'],
            $parameters['package'],
            $parameters['theme'],
            $parameters['store_id']
        );
    }


    /**
     * Returns a list of sources by type
     *
     * @param string $type
     * @return SourceInterface[]
     */
    public function getSources($type = self::INDEX_NORMAL)
    {
        switch ($type) {
            case self::INDEX_NORMAL:
                return $this->getNormalSources();
                break;
            case self::INDEX_RUNTIME:
                return $this->getRuntimeSources();
                break;
        }

        return array();
    }

    /**
     * Returns a list of normal sources
     *
     * @return SourceInterface[]
     */
    protected function getNormalSources()
    {
        $parameters = $this->getDesignParameters();

        $systemFiles = Mage::getModel('ecomdev_layoutcompiler/layout_update_design_files')
            ->getDesignLayoutFiles(
                $this->getDesignPackage(),
                $parameters['area'],
                $parameters['package'],
                $parameters['theme'],
                $parameters['store_id']
            );

        $sources = array();
        foreach ($systemFiles as $file) {
            $sources[] = $this->getObjectFactory()
                ->createInstance('layout_source_file', $file);
        }

        $sources[] = $this->getObjectFactory()
            ->createInstance('layout_source_database', $parameters);

        return $sources;
    }

    /**
     * Returns list of runtime sources
     *
     * @return SourceInterface[]
     */
    protected function getRuntimeSources()
    {
        $sources = array();
        foreach ($this->asArray() as $update) {
            $sources[] = $this->getObjectFactory()
                ->createInstance('layout_source_string', $update);
        }

        return $sources;
    }

    /**
     * Loads index with a specified type of data
     *
     * @param string $type type of the index
     * @return bool
     */
    public function loadIndex($type = self::INDEX_NORMAL)
    {
        return $this->getIndex($type)->load($this->getIndexParams($type));
    }

    /**
     * Returns index parameters used for load and save
     *
     * @param string $type
     * @return string[]
     */
    private function getIndexParams($type = self::INDEX_NORMAL)
    {
        return array('type' => $type) + $this->getDesignParameters();
    }

    /**
     * Returns an instance of index with a specified type
     *
     * @param string $type type of the index
     * @return IndexInterface
     */
    public function getIndex($type = self::INDEX_NORMAL)
    {
        if (!isset($this->index[$type])) {
            $this->index[$type] = $this->getObjectFactory()
                ->createInstance('index');
        }

        return $this->index[$type];
    }


}
