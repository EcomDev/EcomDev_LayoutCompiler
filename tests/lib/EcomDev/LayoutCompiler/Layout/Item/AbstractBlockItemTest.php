<?php

class EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItemTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    /**
     * Include item
     *
     * @var EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    private $layoutItem;

    protected function setUp()
    {
        $this->layoutItem = $this->getMockForAbstractClass(
            'EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem',
            array(
                $this->getAnnotationByName('blockId'),
                $this->getAnnotationByName('parentBlockId', false)
            )
        );
    }

    public function testItsTypeIsInitializeAtInstantiation()
    {
        $this->assertSame(
            EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_LOAD,
            $this->layoutItem->getType()
        );
    }

    /**
     * @blockId test_block_identifier
     */
    public function testItReturnsPassedArgumentsBlockIdentifierPassedInConstructor()
    {
        $this->assertSame(
            'test_block_identifier',
            $this->layoutItem->getBlockId()
        );
    }

    /**
     * @blockId test_block_identifier_one
     * @parentBlockId test_block_parent_identifier_one
     * @parentBlockId test_block_parent_identifier_two
     * @parentBlockId test_block_parent_identifier_three
     */
    public function testItReturnsBlockIdAndParentBlockIdentifiers()
    {
        $this->assertSame(
            array(
                'test_block_identifier_one',
                'test_block_parent_identifier_one',
                'test_block_parent_identifier_two',
                'test_block_parent_identifier_three'
            ),
            $this->layoutItem->getPossibleBlockIdentifiers()
        );
    }

    /**
     * @parentBlockId item_one
     * @parentBlockId item_two
     * @parentBlockId item_three
     */
    public function testItReturnsParentBlockIdentifiers()
    {
        $this->assertSame(
            array('item_one', 'item_two', 'item_three'),
            $this->layoutItem->getParentBlockIds()
        );
    }
}