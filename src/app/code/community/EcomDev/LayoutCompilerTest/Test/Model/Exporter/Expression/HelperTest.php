<?php

class EcomDev_LayoutCompilerTest_Test_Model_Exporter_Expression_HelperTest
    extends PHPUnit_Framework_TestCase
{
    public function testItReturnsExpressionToHelperWithSpecifiedArguments()
    {
        $expression = new EcomDev_LayoutCompiler_Model_Export_Expression_Helper(
            'ecomdev_layoutcompiler', 'someMethod', array('one', 'two')
        );

        $this->assertSame(
            "Mage::helper('ecomdev_layoutcompiler')->someMethod('one', 'two')",
            (string)$expression
        );
    }

    public function testItReturnsExpressionToHelperWithSpecifiedArgumentsThatHaveArray()
    {
        $expression = new EcomDev_LayoutCompiler_Model_Export_Expression_Helper(
            'ecomdev_layoutcompiler', 'someMethod', array('one', array('one', 'two'))
        );

        $this->assertSame(
            "Mage::helper('ecomdev_layoutcompiler')->someMethod('one', array (\n  0 => 'one',\n  1 => 'two',\n))",
            (string)$expression
        );
    }

    public function testItReturnsExpressionToHelperWithNoArguments()
    {
        $expression = new EcomDev_LayoutCompiler_Model_Export_Expression_Helper('ecomdev_layoutcompiler', 'someMethod');

        $this->assertSame(
            "Mage::helper('ecomdev_layoutcompiler')->someMethod()",
            (string)$expression
        );
    }
}
