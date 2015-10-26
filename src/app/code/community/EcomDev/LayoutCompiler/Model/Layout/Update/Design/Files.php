<?php

/**
 * Design files model
 *
 * Moved as a separate class to make update model more testable
 *
 * Methods setConfig and setDesignConfig only used to make it testable
 */
class EcomDev_LayoutCompiler_Model_Layout_Update_Design_Files
{
    /**
     * Configuration model for config
     *
     * @var Mage_Core_Model_Config
     */
    private $config;

    /**
     * Configuration model for design config
     *
     * @var Mage_Core_Model_Design_Config
     */
    private $designConfig;

    /**
     * Returns instance of config model
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->setConfig();
        }

        return $this->config;
    }

    /**
     * Returns instance of design config
     *
     * Design config model is only available since 1.9.x version of Magento,
     * On any other version it will return false
     *
     * @return Mage_Core_Model_Design_Config|bool
     */
    public function getDesignConfig()
    {
        if ($this->designConfig === null) {
            $this->setDesignConfig();
        }

        return $this->designConfig;
    }

    /**
     * Sets a general config model
     *
     * @param null $config
     * @return $this
     */
    public function setConfig($config = null)
    {
        if ($config === null) {
            $config = Mage::getConfig();
        }

        $this->config = $config;
        return $this;
    }

    /**
     * Design config model
     *
     * Available only for Magento 1.9.x,
     * not applicable for earlier versions
     *
     * @param Mage_Core_Model_Design_Config $config
     * @return $this
     */
    public function setDesignConfig($config = null)
    {
        if ($config === null) {
            // Remove exception on standart autoloader of Magento in versions before 1.9.x
            if (@class_exists('Mage_Core_Model_Design_Config')) {
                $config = Mage::getSingleton('core/design_config');
            }
        }

        $this->designConfig = $config;
        return $this;
    }

    /**
     * Return list of files in layout
     *
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param string $storeId
     * @return string[]
     */
    public function getDesignLayoutFiles(
        $designPackage, $area, $package, $theme, $storeId
    )
    {
        $layoutUpdates = $this->getConfig()->getNode(sprintf('%s/layout/updates', $area));
        $nodeFiles = array();

        if ($layoutUpdates) {
            Mage::dispatchEvent('core_layout_update_updates_get_after', array('updates' => $layoutUpdates));
            $nodeFiles = $layoutUpdates->asArray();
        }

        $themeUpdatePath = sprintf('%s/%s/%s/layout/updates', $area, $package, $theme);

        // Reproduce core functionality for theme based updates for 1.9.x only
        if ($this->getDesignConfig()
            && ($themeUpdates = $this->getDesignConfig()->getNode($themeUpdatePath))
            && (is_array($themeUpdates->asArray()))) {
            $nodeFiles = array_merge($nodeFiles, array_values($themeUpdates->asArray()));
        }

        $fileNames = array();

        foreach ($nodeFiles as $file) {
            if (!empty($file['file'])) {
                $module = (!empty($file['@']['module']) ? $file['@']['module'] : false);
                if ($module && Mage::getStoreConfigFlag(
                        sprintf('advanced/modules_disable_output/%s', $module), $storeId
                    )) {
                    continue;
                }
                $fileNames[] = $file['file'];
            }
        }

        $fileNames[] = 'local.xml';

        $files = array();
        foreach ($fileNames as $fileName) {
            $files[] = $designPackage->getLayoutFilename($fileName, array(
                '_area' => $area,
                '_package' => $package,
                '_theme' => $theme
            ));
        }

        return $files;
    }
}
