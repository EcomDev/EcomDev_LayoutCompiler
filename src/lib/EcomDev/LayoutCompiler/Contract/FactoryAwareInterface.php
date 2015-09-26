<?php

use EcomDev_LayoutCompiler_Contract_FactoryInterface as FactoryInterface;

interface EcomDev_LayoutCompiler_Contract_FactoryAwareInterface
{
    /**
     * Returns a factory for objects
     *
     * @return FactoryInterface
     */
    public function getObjectFactory();

    /**
     * Sets a factory for objects created in layout
     *
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setObjectFactory(FactoryInterface $factory);
}
