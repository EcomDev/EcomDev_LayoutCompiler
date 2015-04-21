<?php

use EcomDev_LayoutCompiler_Layout_Item_Block as BlockItem;

class EcomDev_LayoutCompiler_Layout_Item_BlockTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    public function testItPassesBlockIdAndParentIdsIntoParentConstructor()
    {
        $item = new BlockItem(array(), 'block_one', 'parent_block_one', array('parent_item_one', 'parent_item_two'));
        $this->assertSame('block_one', $item->getBlockId());
        $this->assertSame(array('parent_item_one', 'parent_item_two'), $item->getParentBlockIds());
    }

    public function testItStoresOptionsAndParentBlockIdForExecuteAction()
    {
        $item = new BlockItem(array('option_one', 'option_two'), 'block_one', 'parent_block_one');
        $this->assertAttributeSame(array('option_one', 'option_two'), 'options', $item);
        $this->assertAttributeSame('parent_block_one', 'parentBlockId', $item);
    }

    public function testItCreatesANewBlockWithTypeAsClassAlias()
    {
        $item = new BlockItem(array('type' => 'item/one', 'as' => 'child'), 'block_one', 'parent_block_one');
        $processor = $this->createProcessor();
        $layout = $this->createLayout();
        $layout->expects($this->once())
            ->method('newBlock')
            ->with('item/one', 'block_one', array(
                'type' => 'item/one',
                'as' => 'child',
                'parent' => 'parent_block_one'
            ))
            ->willReturnSelf()
        ;
        $this->assertSame($item, $item->execute($layout, $processor));
    }

    public function testItUsesClassInsteadOfTypeIfItIsSpecified()
    {
        $item = new BlockItem(
            array(
                'type' => 'item/one',
                'class' => 'Class_Name',
                'as' => 'child'
            ),
            'block_one', 'parent_block_one'
        );

        $processor = $this->createProcessor();
        $layout = $this->createLayout();
        $layout->expects($this->once())
            ->method('newBlock')
            ->with('Class_Name', 'block_one', array(
                'type' => 'item/one',
                'class' => 'Class_Name',
                'as' => 'child',
                'parent' => 'parent_block_one'
            ))
            ->willReturnSelf();

        $this->assertSame($item, $item->execute($layout, $processor));
    }

    public function testItDoesNotCreateABlockIfTypeOrClassIsNotSpecified()
    {
        $item = new BlockItem(
            array(
                'as' => 'child'
            ),
            'block_one', 'parent_block_one'
        );

        $processor = $this->createProcessor();
        $layout = $this->createLayout();
        $layout->expects($this->never())
            ->method('newBlock');

        $this->assertSame($item, $item->execute($layout, $processor));
    }
}
