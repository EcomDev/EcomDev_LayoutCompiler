<?php

class EcomDev_LayoutCompiler_Model_Observer
{
    private $classAliasesToSet = array(
        'layout' => 'ecomdev_layoutcompiler/layout',
        'layout_update' => 'ecomdev_layoutcompiler/layout_update',
        'layout_processor' => 'EcomDev_LayoutCompiler_Layout_Processor',
        'layout_loader' => 'EcomDev_LayoutCompiler_Layout_Loader',
        'layout_source_file' => 'EcomDev_LayoutCompiler_Layout_Source_File',
        'layout_source_string' => 'EcomDev_LayoutCompiler_Layout_Source_String',
        'layout_source_database' => 'ecomdev_layoutcompiler/layout_source_database',
        'compiler' => 'EcomDev_LayoutCompiler_Compiler',
        'compiler_parser_handle' => 'EcomDev_LayoutCompiler_Compiler_Parser_Handle',
        'compiler_parser_reference' => 'EcomDev_LayoutCompiler_Compiler_Parser_Reference',
        'compiler_parser_remove' => 'EcomDev_LayoutCompiler_Compiler_Parser_Remove',
        'compiler_parser_block' => 'comdev_layoutcompiler/compiler_parser_block',
        'compiler_parser_action' => 'comdev_layoutcompiler/compiler_parser_action',
        'cache' => 'ecomdev_layoutcompiler/cache',
        'exporter' => 'EcomDev_LayoutCompiler_Exporter'
    );

    public function onFactoryInitialize(Varien_Event_Observer $observer)
    {
        /** @var EcomDev_LayoutCompiler_Contract_FactoryInterface $factory */
        $factory = $observer->getFactory();

        foreach ($this->classAliasesToSet as $alias => $className) {
            $factory->setClassAlias($alias, $className);
        }

        $factory->setDependencyInjectionInstruction(
            'EcomDev_LayoutCompiler_Contract_PathAwareInterface',
            'setSavePath',
            Mage::getConfig()->getVarDir('ecomdev/layoutcompiler')
        );
    }
}
