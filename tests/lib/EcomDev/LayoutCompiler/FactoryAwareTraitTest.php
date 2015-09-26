<?php

class EcomDev_LayoutCompiler_FactoryAwareTraitTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_FactoryAwareTrait|EcomDev_LayoutCompiler_Contract_FactoryAwareInterface
     */
    protected $trait;

    protected function setUp()
    {
        $this->trait = $this->getMockForTrait('EcomDev_LayoutCompiler_FactoryAwareTrait');
    }

    public function testItIsPossibleToSetObjectFactory()
    {
        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $this->assertSame($this->trait, $this->trait->setObjectFactory($factory));
        $this->assertAttributeSame($factory, 'objectFactory', $this->trait);
    }

    public function testItIsPossibleToRetrieveObjectFactory()
    {
        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $this->trait->setObjectFactory($factory);
        $this->assertSame($factory, $this->trait->getObjectFactory());
    }
}