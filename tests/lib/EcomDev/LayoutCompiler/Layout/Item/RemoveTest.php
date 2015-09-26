<?php

class EcomDev_LayoutCompiler_Layout_Item_RemoveTest
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
        $this->layoutItem = new EcomDev_LayoutCompiler_Layout_Item_Remove(
            $this->getAnnotationByName('blockId')
        );
    }

    public function testItsTypeIsInitializeAtInstantiation()
    {
        $this->assertSame(
            EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_POST_INITIALIZE,
            $this->layoutItem->getType()
        );
    }

    /**
     * 
     * @blockId block_one
     */
    public function testItRemovesAllOccurrencesOfLoadTypeItemsWithMentionedBlockId()
    {
        $processor = $this->createProcessor();
        $layout = $this->createLayout();
        
        $itemOne = $this->createLayoutItem();
        $itemTwo = $this->createLayoutItem();
        
        $processor->expects($this->once())
            ->method('findItemsByBlockIdAndType')
            ->with('block_one', EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_LOAD)
            ->willReturn(array($itemOne, $itemTwo));

        $processor->expects($this->exactly(2))
            ->method('removeItem')
            ->withConsecutive(
                array($itemOne),
                array($itemTwo)
            )
            ->willReturnSelf();
        
        $this->assertSame(
            $this->layoutItem,
            $this->layoutItem->execute($layout, $processor)
        );
    }
}