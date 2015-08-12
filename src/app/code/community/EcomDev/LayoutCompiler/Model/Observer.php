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
        'compiler_metadata_factory' => 'EcomDev_LayoutCompiler_Compiler_MetadataFactory',
        'compiler_parser_handle' => 'EcomDev_LayoutCompiler_Compiler_Parser_Handle',
        'compiler_parser_reference' => 'EcomDev_LayoutCompiler_Compiler_Parser_Reference',
        'compiler_parser_remove' => 'EcomDev_LayoutCompiler_Compiler_Parser_Remove',
        'compiler_parser_block' => 'ecomdev_layoutcompiler/compiler_parser_block',
        'compiler_parser_action' => 'ecomdev_layoutcompiler/compiler_parser_action',
        'cache' => 'ecomdev_layoutcompiler/cache',
        'index' => 'EcomDev_LayoutCompiler_Index'
    );

    /**
     * Configures factory on the first stage, before anything actually initialized
     *
     * @param Varien_Event_Observer $observer
     */
    public function onFactoryInitialize(Varien_Event_Observer $observer)
    {
        /** @var EcomDev_LayoutCompiler_Contract_FactoryInterface $factory */
        $factory = $observer->getFactory();

        foreach ($this->classAliasesToSet as $alias => $className) {
            $factory->setClassAlias($alias, $className);
        }

        $factory
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_PathAwareInterface',
                'setSavePath',
                Mage::getConfig()->getVarDir('ecomdev/layoutcompiler')
            )
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_ExporterAwareInterface',
                'setExporter',
                new EcomDev_LayoutCompiler_Exporter()
            )
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_FactoryAwareInterface',
                'setObjectFactory',
                $factory
            )
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface',
                'addErrorProcessor',
                Mage::getModel('ecomdev_layoutcompiler/error_processor')
            )
        ;
    }

    public function onLayoutInitialize(Varien_Event_Observer $observer)
    {
        /** @var EcomDev_LayoutCompiler_Contract_FactoryInterface $factory */
        $factory = $observer->getFactory();
        $itemActionClass = Mage::getConfig()->getModelClassName('ecomdev_layoutcompiler/layout_item_action');
        $factory
            ->setDefaultConstructorArguments('compiler_parser_handle', ['EcomDev_LayoutCompiler_Layout_Item_Include'])
            ->setDefaultConstructorArguments('compiler_parser_remove', ['EcomDev_LayoutCompiler_Layout_Item_Remove'])
            ->setDefaultConstructorArguments('compiler_parser_block', ['EcomDev_LayoutCompiler_Layout_Item_Block'])
            ->setDefaultConstructorArguments('compiler_parser_action', [$itemActionClass])
        ;

        Mage::dispatchEvent('ecomdev_layoutcompiler_observer_parsers_init_before', ['factory' => $factory]);

        $data = new stdClass();
        $data->metadata_factory = $factory->createInstance('compiler_metadata_factory');
        $data->parsers = [
            'update' => $factory->createInstance('compiler_parser_handle'),
            'reference' => $factory->createInstance('compiler_parser_reference'),
            'remove' => $factory->createInstance('compiler_parser_remove'),
            'block' => $factory->createInstance('compiler_parser_block'),
            'action' => $factory->createInstance('compiler_parser_action')
        ];

        Mage::dispatchEvent('ecomdev_layoutcompiler_observer_parsers_init_after', [
            'factory' => $factory,
            'data' => $data
        ]);

        $factory
            ->setDefaultConstructorArguments('compiler', [(array)$data])
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_LayoutAwareInterface',
                'setLayout',
                $observer->getLayout()
            )
            ->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_CacheAwareInterface',
                'setCache',
                Mage::getModel('ecomdev_layoutcompiler/cache')
            )
        ;
    }

    public function replaceLayout()
    {
        $manager = Mage::getSingleton('ecomdev_layoutcompiler/manager');
        if ($manager->isEnabled()) {
            $layout = $manager->getLayout();
            if ($layout !== Mage::registry('_singleton/core/layout')) {
                Mage::unregister('_singleton/core/layout');
                Mage::register('_singleton/core/layout', $layout);
                if (Mage::app()->getLayout() !== $layout) {
                    $appReflection = new ReflectionObject(Mage::app());
                    $property = $appReflection->getProperty('_layout');
                    $property->setAccessible(true);
                    $property->setValue(Mage::app(), $layout);
                    $property->setAccessible(false);
                }
            }
        }
    }

    public function disableInTest()
    {
        Mage::getSingleton('ecomdev_layoutcompiler/manager')
            ->disable();
    }
}
