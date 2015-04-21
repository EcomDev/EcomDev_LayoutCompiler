<?php

use EcomDev_LayoutCompiler_Model_Layout_Item_Action as ActionItem;

class EcomDev_LayoutCompilerTest_Test_Model_Layout_Item_ActionTest
    extends PHPUnit_Framework_TestCase
{
    public function testItPassesBlockIdAndParentIdsIntoParentConstructor()
    {
        $item = new ActionItem(array(), 'block_one', function () {}, array('parent_item_one', 'parent_item_two'));
        $this->assertSame('block_one', $item->getBlockId());
        $this->assertSame(array('parent_item_one', 'parent_item_two'), $item->getParentBlockIds());
    }

    public function testItStoresOptionsAndBlockIdForExecuteAction()
    {
        $callback = function () {};
        $item = new ActionItem(array('option_one', 'option_two'), 'block_one', $callback);
        $this->assertAttributeSame(array('option_one', 'option_two'), 'options', $item);
        $this->assertAttributeSame($callback, 'callback', $item);
    }

    public function testItDoesNotCallAClosureIfBlockIsNotFound()
    {
        $callback = function () { $this->fail('The test is failed, no call should be done'); };

        $item = new ActionItem(array('option_one', 'option_two'), 'block_one', $callback);
        $layout = $this->createLayout();
        $processor = $this->createProcessor();
        $layout->expects($this->once())
            ->method('findBlockById')
            ->with('block_one')
            ->willReturn(false);

        $this->assertSame($item, $item->execute($layout, $processor));
    }

    public function testItDoesNotCallAClosureIfConfigurationFlagEvaluatesToFalse()
    {
        $callback = function () { $this->fail('The test is failed, no call should be done'); };

        $item = new ActionItem(array('ifconfig' => 'some/non_defined/flag'), 'block_one', $callback);
        $layout = $this->createLayout();
        $processor = $this->createProcessor();

        $layout->expects($this->never())
            ->method('findBlockById')
            ->with('block_one');

        $this->assertSame($item, $item->execute($layout, $processor));
    }

    /**
     *
     * @loadFixture configuration
     */
    public function testItCallsAClosureIfConfigurationFlagEvaluatesToFalse()
    {
        $block = $this->createBlock();

        $callback = function ($calledBlock) use ($block) {
            $this->assertInstanceOf('Mage_Core_Block_Abstract', $calledBlock);
            $this->assertSame($block, $calledBlock);
        };

        $item = new ActionItem(array('ifconfig' => 'some/defined/flag'), 'block_one', $callback);
        $layout = $this->createLayout();
        $processor = $this->createProcessor();

        $layout->expects($this->once())
            ->method('findBlockById')
            ->with('block_one')
            ->willReturn($block);

        $this->assertSame($item, $item->execute($layout, $processor));
    }

    /**
     * Returns a mock of abstract block instance
     *
     * @return Mage_Core_Block_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    private function createBlock()
    {
        return $this->getMockForAbstractClass('Mage_Core_Block_Abstract');
    }

    /**
     * Returns a mock of processor interface
     *
     * @return EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createProcessor()
    {
        return $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');
    }

    /**
     * Returns a mock of processor interface
     *
     * @return EcomDev_LayoutCompiler_Contract_LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createLayout()
    {
        return $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
    }
}
