<?php

class EcomDev_LayoutCompilerTest_Test_Config_HelperTest
    extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testItHasDefaultHelperDefined()
    {
        $this->assertHelperAlias('ecomdev_layoutcompiler', 'EcomDev_LayoutCompiler_Helper_Data');
    }
}
