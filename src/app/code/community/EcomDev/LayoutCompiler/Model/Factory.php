<?php

/**
 * Factory class for layout objects
 *
 *
 */
class EcomDev_LayoutCompiler_Model_Factory
    extends EcomDev_LayoutCompiler_AbstractFactory
{
    /**
     * Resolves a class name via internal service locator
     *
     * @param string $className
     * @return string|bool
     */
    protected function resolveClassName($className)
    {
        return Mage::getConfig()->getModelClassName($className);
    }
}
