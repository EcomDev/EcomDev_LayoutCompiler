<?php

use EcomDev_LayoutCompiler_Layout_Processor as LayoutProcessor;
use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;

class EcomDev_LayoutCompiler_Layout_ProcessorTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;
    
    /**
     * @var LayoutProcessor
     */
    private $processor;
    
    protected function setUp()
    {
        $this->processor = new LayoutProcessor();
    }
    
    public function testItAddsItemsIntoProcessorByType()
    {
        $item = $this->createLayoutItem(ItemInterface::TYPE_POST_INITIALIZE);
        
        $this->assertSame($this->processor, $this->processor->addItem($item));
        $this->assertSame(
            array($item), 
            $this->processor->findItemsByType(ItemInterface::TYPE_POST_INITIALIZE)
        );
    }
    
    public function testItAddsBlockAwareItemListSoFindMethodsWorkWithoutIssues()
    {
        $item1 = $this->createBlockAwareLayoutItem(array('block_one', array('block_two', 'block_three')));
        $item2 = $this->createBlockAwareLayoutItem(array('block_two', array('block_three')));
        
        $this->assertSame($this->processor, $this->processor->addItem($item1));
        $this->assertSame($this->processor, $this->processor->addItem($item2));
        $this->assertSame(
            array($item1, $item2),
            $this->processor->findItemsByType(ItemInterface::TYPE_LOAD)
        );
        
        $this->assertSame(array($item1, $item2), $this->processor->findItemsByBlockIdAndType(
            'block_two', ItemInterface::TYPE_LOAD
        ));
        
        $this->assertSame(array($item1), $this->processor->findItemsByBlockIdAndType(
            'block_one', ItemInterface::TYPE_LOAD
        ));
        
        $this->assertEmpty($this->processor->findItemsByBlockIdAndType(
            'block_one', ItemInterface::TYPE_POST_INITIALIZE
        ));
        
        $this->assertSame(array($item1, $item2), $this->processor->findItemsByBlockId('block_three'));
    }

    public function testItAddsBlockAwareItemListButIgnoresItsRelationsIfFlagIsSet()
    {
        $item1 = $this->createBlockAwareLayoutItem(array('block_one', array('block_two', 'block_three')));
        $item2 = $this->createBlockAwareLayoutItem(array('block_two', array('block_three')));

        $this->assertSame($this->processor, $this->processor->addItem($item1, false));
        $this->assertSame($this->processor, $this->processor->addItem($item2, false));
        $this->assertSame(
            array($item1, $item2),
            $this->processor->findItemsByType(ItemInterface::TYPE_LOAD)
        );

        $this->assertEmpty($this->processor->findItemsByBlockIdAndType(
            'block_two', ItemInterface::TYPE_LOAD
        ));

        $this->assertEmpty($this->processor->findItemsByBlockIdAndType(
            'block_one', ItemInterface::TYPE_LOAD
        ));

        $this->assertEmpty($this->processor->findItemsByBlockIdAndType(
            'block_one', ItemInterface::TYPE_POST_INITIALIZE
        ));

        $this->assertEmpty(
            $this->processor->findItemsByBlockId('block_three')
        );
    }

    public function testItAddsItemRelationIfNeeded()
    {
        $item = $this->createLayoutItem(ItemInterface::TYPE_POST_INITIALIZE, 3);
        $this->processor->addItem($item);
        $this->processor->addItemRelation($item, 'block_one');
        $this->processor->addItemRelation($item, 'block_three');

        $this->assertSame(
            array($item),
            $this->processor->findItemsByType(ItemInterface::TYPE_POST_INITIALIZE)
        );

        $this->assertSame(
            array($item),
            $this->processor->findItemsByBlockIdAndType(
                'block_one',
                ItemInterface::TYPE_POST_INITIALIZE
            )
        );

        $this->assertSame(
            array($item),
            $this->processor->findItemsByBlockIdAndType(
                'block_three',
                ItemInterface::TYPE_POST_INITIALIZE
            )
        );

        $this->assertEmpty(
            $this->processor->findItemsByBlockIdAndType(
                'block_five',
                ItemInterface::TYPE_POST_INITIALIZE
            )
        );
    }

    public function testItRemovesItemFromAllAddedPlaces()
    {
        $itemToRemove = $this->createBlockAwareLayoutItem(array('block_one', array('block_two')));
        $item = $this->createBlockAwareLayoutItem(array('block_one', array('block_two', 'block_three')));
        $itemToRemoveTwo = $this->createLayoutItem();
        $itemToRemoveTwo->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(ItemInterface::TYPE_POST_INITIALIZE);

        $this->processor->addItem($itemToRemove)
            ->addItem($item)
            ->addItem($itemToRemoveTwo);
        
        $this->assertSame($this->processor, $this->processor->removeItem($itemToRemove));
        $this->assertSame($this->processor, $this->processor->removeItem($itemToRemoveTwo));
        
        $this->assertSame(array($item), $this->processor->findItemsByType(ItemInterface::TYPE_LOAD));
        $this->assertSame(array($item), $this->processor->findItemsByBlockId('block_one'));
        $this->assertSame(array($item), $this->processor->findItemsByBlockId('block_two'));
        
        $this->assertSame(array($item), $this->processor->findItemsByBlockIdAndType(
            'block_two', ItemInterface::TYPE_LOAD
        ));
        
        $this->assertSame(array(), $this->processor->findItemsByType(ItemInterface::TYPE_POST_INITIALIZE));
    }
    
    public function testItExecutesInitializeTypeOnAddDoesNotAddItToInternalStorage()
    {
        $layout = $this->createLayout();
        $layoutItem = $this->createLayoutItem(ItemInterface::TYPE_INITIALIZE);
            
        $layoutItem->expects($this->once())
            ->method('execute')
            ->with($layout, $this->processor);
        
        $this->processor->setLayout($layout);
        $this->processor->addItem($layoutItem);
        
        $this->assertEmpty($this->processor->findItemsByType(ItemInterface::TYPE_INITIALIZE));
    }
    
    public function testItExecutesItemsButDoesNotRemoveThem()
    {
        $layout = $this->createLayout();
        $layoutItemOne = $this->createLayoutItem(ItemInterface::TYPE_POST_INITIALIZE);
        $layoutItemTwo = $this->createLayoutItem(ItemInterface::TYPE_POST_INITIALIZE);
        $layoutItemThree = $this->createLayoutItem(ItemInterface::TYPE_LOAD);

        $layoutItemOne->expects($this->once())
            ->method('execute')
            ->with($layout, $this->processor)
            ->willReturnSelf();
        
        $layoutItemTwo->expects($this->once())
            ->method('execute')
            ->with($layout, $this->processor);
        
        $this->processor
            ->setLayout($layout)
            ->addItem($layoutItemOne)
            ->addItem($layoutItemTwo)
            ->addItem($layoutItemThree);
        
        $this->assertSame(
            $this->processor, $this->processor->execute(ItemInterface::TYPE_POST_INITIALIZE)
        );
        
        $this->assertSame(
            array($layoutItemOne, $layoutItemTwo),
            $this->processor->findItemsByType(ItemInterface::TYPE_POST_INITIALIZE)
        );
        
        $this->assertSame(
            array($layoutItemThree),
            $this->processor->findItemsByType(ItemInterface::TYPE_LOAD)
        );
    }
    
    public function testItRemovesAllItemsFromProcessorWhenResetMethodIsCalled()
    {
        $layoutItem = $this->createLayoutItem(ItemInterface::TYPE_LOAD);
        $this->processor->addItem($layoutItem);
        $this->assertSame($this->processor, $this->processor->reset());
        $this->assertEmpty($this->processor->findItemsByType(ItemInterface::TYPE_LOAD));
    }
}
