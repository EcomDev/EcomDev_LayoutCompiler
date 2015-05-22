<?php

class EcomDev_LayoutCompilerTest_Test_Config_ObserverTest
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testItObservesFactoryInitializeProcess()
    {
        $this->assertEventObserverDefined(
            'global',
            'ecomdev_layoutcompiler_factory_init',
            'ecomdev_layoutcompiler/observer',
            'onFactoryInitialize'
        );
    }

    public function testItObservesLayoutInitializeProcess()
    {
        $this->assertEventObserverDefined(
            'global',
            'ecomdev_layoutcompiler_layout_init',
            'ecomdev_layoutcompiler/observer',
            'onLayoutInitialize'
        );
    }

    public function testItDisablesFunctionalityOnTestArea()
    {
        $this->assertEventObserverDefined(
            'test',
            'controller_front_init_routers',
            'ecomdev_layoutcompiler/observer',
            'disableInTest'
        );
    }

    public function testItReplacesLayoutOnEveryPredispatch()
    {
        $this->assertEventObserverDefined(
            'global',
            'controller_action_predispatch',
            'ecomdev_layoutcompiler/observer',
            'replaceLayout'
        );
    }
}
