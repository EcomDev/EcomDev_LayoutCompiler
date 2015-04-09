<?php

class EcomDev_LayoutCompiler_LayoutAwareTraitTest 
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_LayoutAwareTrait|EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
     */
    protected $trait;
    
    protected function setUp()
    {
        $this->trait = $this->getMockForTrait('EcomDev_LayoutCompiler_LayoutAwareTrait');
    }
    
    public function testItIsPossibleToSetLayout()
    {
        $layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
        $this->trait->setLayout($layout);
        $this->assertAttributeSame($layout, 'layout', $this->trait);
    }
    
    public function testItIsPossibleToRetrieveLayoutObject()
    {
        $layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
        $this->trait->setLayout($layout);
        $this->assertSame($layout, $this->trait->getLayout());
    }
}