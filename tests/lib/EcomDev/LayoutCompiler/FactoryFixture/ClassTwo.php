<?php

/**
 * A fixture class for test of factory
 *
 */
class EcomDev_LayoutCompiler_FactoryFixture_ClassTwo
    extends EcomDev_LayoutCompiler_FactoryFixture_ClassOne
    implements EcomDev_LayoutCompiler_FactoryFixture_OneInterface
{
    private $someData;

    public function setSomeData($data)
    {
        $this->someData = $data;
        return $this;
    }
}
