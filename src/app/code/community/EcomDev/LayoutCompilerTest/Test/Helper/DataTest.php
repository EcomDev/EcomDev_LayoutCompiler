<?php

use EcomDev_LayoutCompiler_Helper_Data as HelperData;

/**
 * Helper class test case
 *
 */
class EcomDev_LayoutCompilerTest_Test_Helper_DataTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var EcomDev_LayoutCompiler_Helper_Data
     */
    private $helper;
    private $originalValue;

    protected function setUp()
    {
        $this->helper = new HelperData();
        $this->originalValue = $this->app()->getStore()
            ->getConfig(HelperData::XML_PATH_IS_ENABLED);
    }

    protected function tearDown()
    {
        $this->app()->getStore()
            ->setConfig(HelperData::XML_PATH_IS_ENABLED, $this->originalValue);
    }

    public function testItReturnsTrueForIsEnabledFlagBasedOnConfigurationValue()
    {
        $this->app()->getStore()->setConfig(HelperData::XML_PATH_IS_ENABLED, '1');
        $this->assertTrue($this->helper->isEnabled());
        $this->app()->getStore()->setConfig(HelperData::XML_PATH_IS_ENABLED, '0');
        $this->assertFalse($this->helper->isEnabled());
    }
}
