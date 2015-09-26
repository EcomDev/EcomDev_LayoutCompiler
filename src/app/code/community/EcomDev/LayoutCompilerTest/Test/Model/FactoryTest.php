<?php

class EcomDev_LayoutCompilerTest_Test_Model_FactoryTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new EcomDev_LayoutCompiler_Model_Factory();
    }

    public function testItResolvesModelClassAliases()
    {
        $instance = $this->factory->createInstance('ecomdev_layoutcompiler/factory');
        $this->assertInstanceOf(
            'EcomDev_LayoutCompiler_Model_Factory',
            $instance
        );

        $this->assertNotSame(
            $this->factory,
            $instance
        );
    }
}
