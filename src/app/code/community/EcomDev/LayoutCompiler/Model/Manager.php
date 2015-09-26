<?php

use EcomDev_LayoutCompiler_Contract_FactoryInterface as FactoryInterface;
use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;

/**
 * Manager model for layout compiler
 */
class EcomDev_LayoutCompiler_Model_Manager
{
    /**
     * Factory instance
     *
     * @var FactoryInterface
     */
    private $factory;

    /**
     * Layout instance
     *
     * @var LayoutInterface
     */
    private $layout;

    /**
     * Flag for allowing or disallowing compiler functionality
     *
     * @var bool
     */
    private $isEnabled = true;

    /**
     * Returns an instance of factory model
     *
     * @return FactoryInterface
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = Mage::getModel('ecomdev_layoutcompiler/factory');
            Mage::dispatchEvent('ecomdev_layoutcompiler_factory_init', array('factory' => $this->factory));
        }

        return $this->factory;
    }

    /**
     * Sets a factory instance
     *
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Returns a layout instance
     *
     * @return LayoutInterface
     */
    public function getLayout()
    {
        if ($this->layout === null) {
            $this->layout = $this->getFactory()->createInstance('layout');
            Mage::dispatchEvent('ecomdev_layoutcompiler_layout_init', array(
                'factory' => $this->factory,
                'layout' => $this->layout
            ));
        }

        return $this->layout;
    }

    /**
     * Disables layout compiler functionality
     *
     * @return $this
     */
    public function disable()
    {
        $this->isEnabled = false;
        return $this;
    }

    /**
     * Checks if functionality is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->isEnabled) {
            return Mage::helper('ecomdev_layoutcompiler')->isEnabled();
        }
        return $this->isEnabled;
    }
}
