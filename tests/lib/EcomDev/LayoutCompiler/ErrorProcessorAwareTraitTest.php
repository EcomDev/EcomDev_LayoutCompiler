<?php

class EcomDev_LayoutCompiler_ErrorProcessorAwareTraitTest 
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_ErrorProcessorAwareTrait|EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface
     */
    protected $trait;
    
    protected function setUp()
    {
        $this->trait = $this->getMockForTrait('EcomDev_LayoutCompiler_ErrorProcessorAwareTrait');
    }
    
    public function testItIsPossibleToAddErrorProcessorToObject()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $this->trait->addErrorProcessor($processor);
        $this->assertAttributeSame(array($processor), 'errorProcessors', $this->trait);
    }

    public function testItIsPossibleToAddErrorProcessorOnlyOnce()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $this->trait->addErrorProcessor($processor);
        $this->trait->addErrorProcessor($processor);
        $this->assertAttributeSame(array($processor), 'errorProcessors', $this->trait);
    }

    public function testItIsPossibleToAddMultipleProcessors()
    {
        $processorOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $processorTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $this->trait->addErrorProcessor($processorOne);
        $this->trait->addErrorProcessor($processorTwo);
        $this->assertAttributeSame(array($processorOne, $processorTwo), 'errorProcessors', $this->trait);
    }
    
    public function testItReportsErrorToAllErrorProcessorsEvenIfOneFails()
    {
        $processorOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $processorTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        $processorThree = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface');
        
        $this->trait->addErrorProcessor($processorOne);
        $this->trait->addErrorProcessor($processorTwo);
        $this->trait->addErrorProcessor($processorThree);
        
        $testException = new RuntimeException('Test exception');
        
        $processorOne->expects($this->once())
            ->method('processException')
            ->with($testException)
            ->willReturnSelf();
        
        $processorTwo->expects($this->once())
            ->method('processException')
            ->with($testException)
            ->willThrowException(new RuntimeException('Test exception'));
        
        $processorThree->expects($this->once())
            ->method('processException')
            ->with($testException)
            ->willReturnSelf();
        
        $this->assertSame($this->trait, $this->trait->reportException($testException));
    }

}