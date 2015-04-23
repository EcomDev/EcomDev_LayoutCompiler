<?php

class EcomDev_LayoutCompilerTest_Test_Model_ObserverTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Observer instance
     *
     * @var EcomDev_LayoutCompiler_Model_Observer
     */
    private $observer;

    protected function setUp()
    {
        $this->observer = new EcomDev_LayoutCompiler_Model_Observer();
    }

    public function testItSpecifiesDefaultConfigurationForClassAliasesAndDefaultSavePath()
    {
        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $factory->expects($this->exactly(15))
            ->method('setClassAlias')
            ->withConsecutive(
                ['layout', 'ecomdev_layoutcompiler/layout'],
                ['layout_update', 'ecomdev_layoutcompiler/layout_update'],
                ['layout_processor', 'EcomDev_LayoutCompiler_Layout_Processor'],
                ['layout_loader', 'EcomDev_LayoutCompiler_Layout_Loader'],
                ['layout_source_file', 'EcomDev_LayoutCompiler_Layout_Source_File'],
                ['layout_source_string', 'EcomDev_LayoutCompiler_Layout_Source_String'],
                ['layout_source_database', 'ecomdev_layoutcompiler/layout_source_database'],
                ['compiler', 'EcomDev_LayoutCompiler_Compiler'],
                ['compiler_metadata_factory', 'EcomDev_LayoutCompiler_Compiler_MetadataFactory'],
                ['compiler_parser_handle', 'EcomDev_LayoutCompiler_Compiler_Parser_Handle'],
                ['compiler_parser_reference', 'EcomDev_LayoutCompiler_Compiler_Parser_Reference'],
                ['compiler_parser_remove', 'EcomDev_LayoutCompiler_Compiler_Parser_Remove'],
                ['compiler_parser_block', 'ecomdev_layoutcompiler/compiler_parser_block'],
                ['compiler_parser_action', 'ecomdev_layoutcompiler/compiler_parser_action'],
                ['cache', 'ecomdev_layoutcompiler/cache']
            )
            ->willReturnSelf()
        ;

        $expectedVarDirectory = Mage::getBaseDir('var') . DS . 'ecomdev' . DS . 'layoutcompiler';

        $factory->expects($this->exactly(2))
            ->method('setDependencyInjectionInstruction')
            ->withConsecutive(
                array(
                    'EcomDev_LayoutCompiler_Contract_PathAwareInterface',
                    'setSavePath',
                    $expectedVarDirectory
                ),
                array(
                    'EcomDev_LayoutCompiler_Contract_ExporterAwareInterface',
                    'setExporter',
                    $this->isInstanceOf('EcomDev_LayoutCompiler_Contract_ExporterInterface')
                )
            )
            ->willReturnSelf();

        $observer = $this->generateObserver(['factory' => $factory], 'ecomdev_layoutcompiler_factory_init');
        $this->observer->onFactoryInitialize($observer);
    }

    public function testItSpecifiesDefaultArgumentForCompilerOnLayoutInitialization()
    {
        $metadataFactory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface');
        $layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
        $parsers = [
            'handle' => $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface'),
            'reference' => $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface'),
            'remove' => $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface'),
            'block' => $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface'),
            'action' => $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface')
        ];

        $factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $factory->expects($this->exactly(5))
            ->method('setDefaultConstructorArguments')
            ->withConsecutive(
                ['compiler_parser_handle', ['EcomDev_LayoutCompiler_Layout_Item_Handle']],
                ['compiler_parser_remove', ['EcomDev_LayoutCompiler_Layout_Item_Remove']],
                ['compiler_parser_block', ['EcomDev_LayoutCompiler_Layout_Item_Block']],
                ['compiler_parser_action', ['EcomDev_LayoutCompiler_Model_Layout_Item_Action']],
                ['compiler', [[
                    'metadata_factory' => $metadataFactory,
                    'parsers' => $parsers
                ]]]
            )
            ->willReturnSelf()
        ;


        $factory->expects($this->exactly(6))
            ->method('createInstance')
            ->withConsecutive(
                ['compiler_metadata_factory'],
                ['compiler_parser_handle'],
                ['compiler_parser_reference'],
                ['compiler_parser_remove'],
                ['compiler_parser_block'],
                ['compiler_parser_action']
            )
            ->willReturnOnConsecutiveCalls(
                $metadataFactory,
                $parsers['handle'],
                $parsers['reference'],
                $parsers['remove'],
                $parsers['block'],
                $parsers['action']
            )
        ;

        $factory->expects($this->exactly(2))
            ->method('setDependencyInjectionInstruction')
            ->withConsecutive(
                ['EcomDev_LayoutCompiler_Contract_LayoutAwareInterface', 'setLayout', $layout],
                ['EcomDev_LayoutCompiler_Contract_CacheAwareInterface', 'setCache', $this->isInstanceOf(
                    'EcomDev_LayoutCompiler_Contract_CacheInterface'
                )]
            )
            ->willReturnSelf();

        $observer = $this->generateObserver(
            ['factory' => $factory, 'layout' => $layout],
            'ecomdev_layoutcompiler_factory_layout_init'
        );

        $this->observer->onLayoutInitialize($observer);

        $this->assertEventDispatched('ecomdev_layoutcompiler_observer_parsers_init_before');
        $this->assertEventDispatched('ecomdev_layoutcompiler_observer_parsers_init_after');
    }
}
