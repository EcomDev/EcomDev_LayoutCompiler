<?php

class EcomDev_LayoutCompilerTest_Test_Model_ManagerTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Manager model for layout compiler model
     *
     * @var EcomDev_LayoutCompiler_Model_Manager
     */
    private $manager;

    protected function setUp()
    {
        $this->manager = new EcomDev_LayoutCompiler_Model_Manager();
        $this->app()->disableEvents();
    }

    public function testItInstantiatesAFactoryModelAndDispatchesEventOnlyTheFirstTimeWhenGetFactoryMethodIsCalled()
    {
        $factory = $this->manager->getFactory();
        $this->assertSame($factory, $this->manager->getFactory());
        $this->assertEventDispatchedExactly('ecomdev_layoutcompiler_factory_init', 1);
    }

    public function testItIsPossibleToOverrideFactoryInstance()
    {
        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $this->assertSame($this->manager, $this->manager->setFactory($factory));
        $this->assertSame($factory, $this->manager->getFactory());
    }

    public function testItInstantiatesLayoutModelViaFactoryAndOnlyOnce()
    {
        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
        $this->manager->setFactory($factory);
        $factory->expects($this->once())
            ->method('createInstance')
            ->with('layout')
            ->willReturn($layout);

        $this->assertSame($layout, $this->manager->getLayout());
        $this->assertSame($layout, $this->manager->getLayout());
        $this->assertEventDispatchedExactly('ecomdev_layoutcompiler_layout_init', 1);
    }

    public function testItReturnsTrueIfHelperReturnsTrue()
    {
        $this->mockHelper('ecomdev_layoutcompiler')
            ->replaceByMock('helper')
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->assertTrue($this->manager->isEnabled());
    }

    public function testItReturnsFalseIfHelperReturnsFalse()
    {
        $this->mockHelper('ecomdev_layoutcompiler')
            ->replaceByMock('helper')
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->assertFalse($this->manager->isEnabled());
    }

    public function testItReturnsFalseIfManagerIsDisabled()
    {
        $this->mockHelper('ecomdev_layoutcompiler')
            ->replaceByMock('helper')
            ->expects($this->never())
            ->method('isEnabled');

        $this->manager->disable();

        $this->assertFalse($this->manager->isEnabled());
    }

    protected function tearDown()
    {
        $this->app()->enableEvents();
    }
}
