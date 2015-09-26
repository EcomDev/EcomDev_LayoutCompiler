<?php

class EcomDev_LayoutCompilerTest_Test_Config_ModelsTest
    extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @param string $classAlias
     * @param string $expectedClass
     * 
     * @dataProvider dataProviderModelAliases
     */
    public function testItHasModelAliasDefinedForAModel($classAlias, $expectedClass)
    {
        $this->assertModelAlias($classAlias, $expectedClass);
    }
    
    public function dataProviderModelAliases()
    {
        return array(
            'layout_model' => array(
                'ecomdev_layoutcompiler/layout',
                'EcomDev_LayoutCompiler_Model_Layout'
            ),
            'layout_update_model' => array(
                'ecomdev_layoutcompiler/layout_update',
                'EcomDev_LayoutCompiler_Model_Layout_Update'
            ),
            'layout_source_database' => array(
                'ecomdev_layoutcompiler/layout_source_database',
                'EcomDev_LayoutCompiler_Model_Layout_Source_Database'
            ),
            'factory' => array(
                'ecomdev_layoutcompiler/factory',
                'EcomDev_LayoutCompiler_Model_Factory'
            ),
            'compiler_parser_block_model' => array(
                'ecomdev_layoutcompiler/compiler_parser_block',
                'EcomDev_LayoutCompiler_Model_Compiler_Parser_Block'
            ),
            'compiler_parser_action_model' => array(
                'ecomdev_layoutcompiler/compiler_parser_action',
                'EcomDev_LayoutCompiler_Model_Compiler_Parser_Action'
            ),
            'cache_model' => array(
                'ecomdev_layoutcompiler/cache',
                'EcomDev_LayoutCompiler_Model_Cache'
            )
        );
    }

    /**
     * @param string $classAlias
     * @param string $expectedClass
     *
     * @dataProvider dataProviderResourceModelAliases
     */
    public function testItHasResourceModelAliasDefinedForAModel($classAlias, $expectedClass)
    {
        $this->assertResourceModelAlias($classAlias, $expectedClass);
    }

    public function dataProviderResourceModelAliases()
    {
        return array(
            'layout_source_database_resource_model' => array(
                'ecomdev_layoutcompiler/layout_source_database',
                'EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database'
            )
        );
    }
}
