<?php

use EcomDev_LayoutCompiler_Contract_FactoryInterface as FactoryInterface;

class EcomDev_LayoutCompilerTest_Test_Model_LayoutTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Layout
     */
    private $layout;

    /**
     * @var FactoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->layout = new EcomDev_LayoutCompiler_Model_Layout();
        $this->factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $this->layout->setObjectFactory($this->factory);
    }

    public function testItCreatesACompilerViaFactoryAndInstantiatesItOnlyOnce()
    {
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $this->factory->expects($this->once())
            ->method('createInstance')
            ->with('compiler')
            ->willReturn($compiler);

        $this->assertSame($compiler, $this->layout->getCompiler());
        $this->assertSame($compiler, $this->layout->getCompiler());
    }

    public function testItCreatesAProcessorFromFactory()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');

        $this->factory->expects($this->once())
            ->method('createInstance')
            ->with('layout_processor')
            ->willReturn($processor)
        ;

        $this->assertSame($processor, $this->layout->getProcessor());
        $this->assertSame($processor, $this->layout->getProcessor());
    }

    public function testItCreatesALoaderFromFactory()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface');

        $this->factory->expects($this->once())
            ->method('createInstance')
            ->with('layout_loader')
            ->willReturn($processor)
        ;

        $this->assertSame($processor, $this->layout->getLoader());
        $this->assertSame($processor, $this->layout->getLoader());
    }

    public function testItReturnsFalseIfBlockIsNotRegistered()
    {
        $this->assertFalse(
            $this->layout->findBlockById('item_id')
        );
    }

    public function testItReturnsBlockByIdIfItExists()
    {
        $block = $this->layout->createBlock('core/text', 'item_id');

        $this->assertSame(
            $block,
            $this->layout->findBlockById('item_id')
        );
    }

    public function testItCreatesANewBlockInParentBlock()
    {
        $parentBlock = $this->layout->createBlock('core/text', 'item_one');
        $block = $this->layout->newBlock('core/text', 'item_two', ['parent' => 'item_one']);
        $this->assertSame($block, $parentBlock->getChild('item_two'));
        $this->assertSame($parentBlock, $block->getParentBlock());
    }

    public function testItCreatesANewBlockEvenIfParentBlockIsNotFound()
    {
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock('core/text', 'item_two', ['parent' => 'item_one']);
        $this->assertNull($block->getParentBlock());
    }

    public function testItMovesAnItemBeforeAnotherItem()
    {
        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element(
            '<node><block type="core/text" name="item_one"><block type="core/text" name="item_zero" />'
            . '<block type="core/text" name="item_three" /></block></node>'
        ));
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock(
            'core/text', 'item_two',
            ['parent' => 'item_one', 'before' => 'item_three']
        );
        $parentBlock = $this->layout->getBlock('item_one');
        $this->assertSame($parentBlock, $block->getParentBlock());
        $this->assertSame(['item_zero', 'item_two', 'item_three'], $parentBlock->getSortedChildren());
    }

    public function testItMovesAnItemBeforeAllItems()
    {
        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element(
            '<node><block type="core/text" name="item_one"><block type="core/text" name="item_zero" />'
            . '<block type="core/text" name="item_three" /></block></node>'
        ));
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock(
            'core/text', 'item_two',
            ['parent' => 'item_one', 'before' => '-']
        );
        $parentBlock = $this->layout->getBlock('item_one');
        $this->assertSame($parentBlock, $block->getParentBlock());
        $this->assertSame(['item_two', 'item_zero', 'item_three'], $parentBlock->getSortedChildren());
    }

    public function testItMovesAnItemAfterAnotherItem()
    {
        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element(
            '<node><block type="core/text" name="item_one"><block type="core/text" name="item_zero" />'
            . '<block type="core/text" name="item_three" /></block></node>'
        ));
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock(
            'core/text', 'item_two',
            ['parent' => 'item_one', 'after' => 'item_zero']
        );
        $parentBlock = $this->layout->getBlock('item_one');
        $this->assertSame($parentBlock, $block->getParentBlock());
        $this->assertSame(['item_zero', 'item_two', 'item_three'], $parentBlock->getSortedChildren());
    }

    public function testItMovesAnItemAfterAllItems()
    {
        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element(
            '<node><block type="core/text" name="item_one"><block type="core/text" name="item_zero" />'
            . '<block type="core/text" name="item_three" /></block></node>'
        ));
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock(
            'core/text', 'item_two',
            ['parent' => 'item_one', 'after' => '-']
        );
        $parentBlock = $this->layout->getBlock('item_one');
        $this->assertSame($parentBlock, $block->getParentBlock());
        $this->assertSame(['item_zero', 'item_three', 'item_two'], $parentBlock->getSortedChildren());
    }

    public function testItSetsAnAliasForACreatedBlock()
    {
        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element(
            '<node><block type="core/text" name="item_one"><block type="core/text" name="item_zero" />'
            . '<block type="core/text" name="item_three" /></block></node>'
        ));
        /** @var Mage_Core_Block_Text $block */
        $block = $this->layout->newBlock(
            'core/text', 'item_two',
            ['parent' => 'item_one', 'as' => 'two']
        );
        $parentBlock = $this->layout->getBlock('item_one');
        $this->assertSame($parentBlock, $block->getParentBlock());
        $this->assertSame($block, $parentBlock->getChild('two'));
    }

    public function testItAddsABlockAsAnOutputBlockWithSpecifiedMethod()
    {
        $this->assertInstanceOf(
            'Mage_Core_Block_Text',
            $this->layout->newBlock(
                'core/text',
                'new_block',
                ['output' => 'toHtml']
            )
        );

        $this->assertSame(
            ['new_block' => ['new_block', 'toHtml']],
            $this->layout->getOutputBlockList()
        );
    }

    public function testItIgnoresOutputParameterIfItIsNotSpecified()
    {
        $this->assertInstanceOf(
            'Mage_Core_Block_Text',
            $this->layout->newBlock(
                'core/text',
                'new_block',
                []
            )
        );

        $this->assertSame(
            [],
            $this->layout->getOutputBlockList()
        );
    }

    public function testItSetsTemplateIfItIsSuppliedInOptions()
    {
        $block = $this->layout->newBlock(
            'core/text',
            'new_block',
            ['template' => 'some/template/file.phtml']
        );

        $this->assertSame('some/template/file.phtml', $block->getTemplate());
    }

    public function testItRecognizesAnonymousBlockFlag()
    {
        $block = $this->layout->newBlock(
            'core/text', 'does_not_matter',
            ['_ecomdev_system_option' => ['is_anonymous' => true]]
        );

        $this->assertTrue($block->getIsAnonymous());
        $this->assertNull($block->getAnonSuffix());
    }

    public function testItRecognizesAnonymousBlockFlagTogetherWithSuffix()
    {
        $block = $this->layout->newBlock(
            'core/text', 'does_not_matter',
            ['_ecomdev_system_option' => [
                'is_anonymous' => true,
                'anon_suffix' => 'price_block'
            ]]
        );

        $this->assertTrue($block->getIsAnonymous());
        $this->assertSame('price_block', $block->getAnonSuffix());
    }

    public function testItCreatesAnUpdateFromFactory()
    {
        $update = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface');

        $this->factory->expects($this->once())
            ->method('createInstance')
            ->with('layout_update')
            ->willReturn($update)
        ;

        $this->assertSame($update, $this->layout->getUpdate());
        $this->assertSame($update, $this->layout->getUpdate());
    }

    public function testItExecutesRuntimeStatementsInProcessorWhenGenerateXmlIsCalled()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');
        $update = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface');

        $this->factory->expects($this->exactly(2))
            ->method('createInstance')
            ->withConsecutive(
                ['layout_update'],
                ['layout_processor']
            )
            ->willReturnOnConsecutiveCalls($update, $processor);
        ;

        $update->expects($this->once())
            ->method('loadRuntime')
            ->willReturnSelf();


        $processor->expects($this->once())
            ->method('execute')
            ->with(EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_POST_INITIALIZE)
            ->willReturnSelf();

        $this->assertSame($this->layout, $this->layout->generateXml());
    }

    public function testItExecutesLoadMethodWhenGenerateBlocksIsCalled()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');

        $this->factory->expects($this->once())
            ->method('createInstance')
            ->with('layout_processor')
            ->willReturn($processor);

        $processor->expects($this->once())
            ->method('execute')
            ->with(EcomDev_LayoutCompiler_Contract_Layout_ItemInterface::TYPE_LOAD)
            ->willReturnSelf();

        $this->layout->generateBlocks();
    }

    public function testItDoesNotExecuteLoadMethodWhenGenerateBlockIsCalledWithArgument()
    {
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');

        $this->factory->expects($this->never())
            ->method('createInstance')
            ->with('layout_processor')
            ->willReturn($processor);

        $this->layout->generateBlocks(new Mage_Core_Model_Layout_Element('<node />'));
    }
}
