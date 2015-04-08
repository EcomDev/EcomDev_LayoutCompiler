<?php

class EcomDev_LayoutCompiler_PathAwareTraitTest 
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_PathAwareTrait|EcomDev_LayoutCompiler_Contract_PathAwareInterface
     */
    protected $trait;
    
    protected function setUp()
    {
        $this->trait = $this->getMockForTrait('EcomDev_LayoutCompiler_PathAwareTrait');
    }
    
    public function testItIsPossibleToSetSavePath()
    {
        $this->trait->setSavePath('test/path/file');
        $this->assertAttributeSame('test/path/file', 'savePath', $this->trait);
    }
    
    public function testItIsPossibleToRetrieveSavePath()
    {
        $this->trait->setSavePath('test/path/file');
        $this->assertSame('test/path/file', $this->trait->getSavePath());
    }
}
