<?php

use EcomDev_LayoutCompiler_Contract_FactoryInterface as FactoryInterface;

trait EcomDev_LayoutCompiler_FactoryAwareTrait
{
    /**
     * Factory for creating the objects
     * 
     * @var FactoryInterface
     */
    private $objectFactory;
    
    /**
     * Sets object factory into object
     * 
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setObjectFactory(FactoryInterface $factory)
    {
        $this->objectFactory = $factory;
        return $this;
    }

    /**
     * Returns an instance of object factory, that was provided before via setter
     * 
     * @return FactoryInterface
     */
    public function getObjectFactory()
    {
        return $this->objectFactory;
    }
}
