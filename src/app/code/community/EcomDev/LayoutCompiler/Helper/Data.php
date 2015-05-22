<?php

/**
 * Helper for translation and configuration retrieval
 *
 */
class EcomDev_LayoutCompiler_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const XML_PATH_IS_ENABLED = 'dev/template/ecomdev_layoutcompiler';

    /**
     * Checks if module is enabled in configuration
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_IS_ENABLED);
    }
}
