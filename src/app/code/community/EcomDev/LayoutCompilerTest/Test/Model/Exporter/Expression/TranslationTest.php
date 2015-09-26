<?php

class EcomDev_LayoutCompilerTest_Test_Model_Exporter_Expression_TranslationTest
    extends PHPUnit_Framework_TestCase
{
    public function testItReturnsExpressionToWithTranslationOn()
    {
        $expression = new EcomDev_LayoutCompiler_Model_Export_Expression_Translate(
            'ecomdev_layoutcompiler', 'Some text to translate'
        );

        $this->assertSame(
            "Mage::helper('ecomdev_layoutcompiler')->__('Some text to translate')",
            (string)$expression
        );
    }

    public function testItReturnsExpressionPassedAsArgumentAsIs()
    {
        $expression = new EcomDev_LayoutCompiler_Model_Export_Expression_Translate(
            'ecomdev_layoutcompiler', new EcomDev_LayoutCompiler_Exporter_Expression('1*2*3')
        );

        $this->assertSame(
            "Mage::helper('ecomdev_layoutcompiler')->__(1*2*3)",
            (string)$expression
        );
    }
}
