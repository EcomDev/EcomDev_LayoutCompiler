<?php

class EcomDev_LayoutCompiler_Layout_Item_IncludeTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    /**
     * Include item
     * 
     * @var EcomDev_LayoutCompiler_Layout_Item_Include
     */
    private $layoutItem;
    
    protected function setUp()
    {
        $this->layoutItem = new EcomDev_LayoutCompiler_Layout_Item_Include(
            $this->getAnnotationByName('handleName')
        );
    }

    public function testItsTypeIsInitializeAtInstantiation()
    {
        $this->assertSame(
            EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_INITIALIZE,
            $this->layoutItem->getType()
        );
    }

    /**
     * @handleName handle_that_is_not_loaded
     */
    public function testIncludesOtherItemsIntoProcessorWhenHandleIsNotLoaded()
    {
        $processor = $this->createProcessor();
        $loader = $this->createLoader();
        $layout = $this->createLayout($loader);
     
        $loader->expects($this->once())
            ->method('isLoaded')
            ->id('is_loaded')
            ->with('handle_that_is_not_loaded')
            ->willReturn(false);
        
        $loader->expects($this->once())
            ->method('loadIntoProcessor')
            ->with('handle_that_is_not_loaded', $processor)
            ->after('is_loaded')
            ->willReturnSelf();
        
        $this->assertSame($this->layoutItem, $this->layoutItem->execute($layout, $processor));
    }

    /**
     * @handleName handle_that_is_loaded
     */
    public function testItDoesNotIncludeAHandleIfItIsAlreadyLoaded()
    {
        $processor = $this->createProcessor();
        $loader = $this->createLoader();
        $layout = $this->createLayout($loader);

        $loader->expects($this->once())
            ->method('isLoaded')
            ->with('handle_that_is_loaded')
            ->willReturn(true);

        $loader->expects($this->never())
            ->method('loadIntoProcessor');

        $this->assertSame($this->layoutItem, $this->layoutItem->execute($layout, $processor));
    }
}