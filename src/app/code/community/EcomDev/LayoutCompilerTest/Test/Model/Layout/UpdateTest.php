<?php

use EcomDev_LayoutCompiler_Contract_FactoryInterface as FactoryInterface;
use EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface as UpdateInterface;

class EcomDev_LayoutCompilerTest_Test_Model_Layout_UpdateTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Update model under test
     *
     * @var EcomDev_LayoutCompiler_Model_Layout_Update
     */
    private $update;

    /**
     * Factory object mock
     *
     * @var FactoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->update = new EcomDev_LayoutCompiler_Model_Layout_Update();
        $this->factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_FactoryInterface');
        $this->update->setObjectFactory($this->factory);
    }

    public function testItCreatesAnInstanceOfIndexViaFactory()
    {
        $index = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $this->configureCreateInstance($index, 'index');

        $item = $this->update->getIndex('test');
        $this->assertSame($index, $item);
        $this->assertSame($index, $this->update->getIndex('test'));
    }


    /**
     * @singleton core/design_package
     */
    public function testItReturnsDesignPackageFromSingleton()
    {
        $designPackage = Mage::getSingleton('core/design_package');
        $this->assertSame($designPackage, $this->update->getDesignPackage());
    }

    public function testItOverridesDesignPackageIfNeeded()
    {
        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $this->assertSame($this->update, $this->update->setDesignPackage($designPackage));
        $this->assertSame($designPackage, $this->update->getDesignPackage());
    }

    public function testItCreatesANewInstanceForEveryGetIndexArgument()
    {
        $indexOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $indexTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');

        $this->configureCreateInstance([
            [$indexOne, 'index'],
            [$indexTwo, 'index']
        ]);

        $this->assertSame($indexOne, $this->update->getIndex('one'));
        $this->assertSame($indexTwo, $this->update->getIndex('two'));
    }

    public function testItUsesCreatedIndexForLoadingTheDataAndReturnsResultOfLoadMethod()
    {
        $index = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $this->configureCreateInstance($index, 'index');
        $design = $this->getMock('Mage_Core_Model_Design_Package');

        $this->configureDesignPackage($design, 'frontend', 'default', 'default123');

        $this->setCurrentStore('default');
        $this->update->setDesignPackage($design);

        $this->configureIndexMethodCall(
            $index,
            [
                'type' => 'index',
                'area' => 'frontend',
                'package' => 'default',
                'theme' => 'default123',
                'store_id' => '1'
            ],
            'load',
            true
        );

        $this->assertSame(true, $this->update->loadIndex('index'));
    }

    public function testItCreatesSourcesFromDesignFilesAndDatabaseSourceWithParameters()
    {
        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');

        $designFiles = $this->mockModel('ecomdev_layoutcompiler/layout_update_design_files')
            ->replaceByMock('model');

        $this->configureDesignPackage($designPackage, 'frontend', 'default', 'default123');

        $designFiles->expects($this->once())
            ->method('getDesignLayoutFiles')
            ->with($designPackage, 'frontend', 'default', 'default123', '1')
            ->willReturn(['path/to/file1.xml', 'path/to/file2.xml'])
        ;

        $sourceFileOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceFileTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceDb = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');

        $this->configureCreateInstance([
            [$sourceFileOne, 'layout_source_file', 'path/to/file1.xml'],
            [$sourceFileTwo, 'layout_source_file', 'path/to/file2.xml'],
            [$sourceDb, 'layout_source_database', [
                'area' => 'frontend',
                'package' => 'default',
                'theme' => 'default123',
                'store_id' => '1'
            ]]
        ]);

        $this->setCurrentStore('default');

        $this->assertSame(
            [$sourceFileOne, $sourceFileTwo, $sourceDb],
            $this->update->getSources(UpdateInterface::INDEX_NORMAL)
        );
    }

    public function testItReturnsAnEmptyArrayForUnkownSourceType()
    {
        $this->assertSame([], $this->update->getSources('source_type_unknown'));
    }

    public function testItReturnsEmptySourcesFromNoUpdateStringsForRuntime()
    {
        $this->assertSame([], $this->update->getSources(UpdateInterface::INDEX_RUNTIME));
    }

    public function testItReturnsSourcesFromAddedUpdateStringsInRuntime()
    {
        $this->update->addUpdate('<xml><node1></node1></xml>');
        $this->update->addUpdate('<xml><node2></node2></xml>');
        $sourceOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');

        $this->configureCreateInstance([
            [$sourceOne, 'layout_source_string', '<xml><node1></node1></xml>'],
            [$sourceTwo, 'layout_source_string', '<xml><node2></node2></xml>']
        ]);

        $this->assertSame(
            [$sourceOne, $sourceTwo],
            $this->update->getSources(UpdateInterface::INDEX_RUNTIME)
        );
    }

    public function testItLoadsRuntimeSourcesIntoRuntimeIndexAllTheTime()
    {
        $index = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $this->update->addUpdate('<xml><node1></node1></xml>');
        $this->update->addUpdate('<xml><node2></node2></xml>');
        $sourceOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');

        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $this->configureDesignPackage($designPackage, 'frontend', 'default', 'default123');

        $sourceOne->expects($this->once())
            ->method('getId')
            ->willReturn('string_1');

        $sourceTwo->expects($this->once())
            ->method('getId')
            ->willReturn('string_2');

        $this->configureCreateInstance([
            [$sourceOne, 'layout_source_string', '<xml><node1></node1></xml>'],
            [$sourceTwo, 'layout_source_string', '<xml><node2></node2></xml>'],
            [$index, 'index']
        ]);

        $this->configureIndexMethodCall(
            $index,
            [$sourceOne, $sourceTwo],
            'update',
            $this->returnSelf()
        );

        $this->configureIndexMethodCall(
            $index,
            ['type' => 'runtime',
             'area' => 'frontend',
             'package' => 'default',
             'theme' => 'default123',
             'store_id' => '1'],
            'load',
            true
        );

        $this->configureIndexMethodCall(
            $index,
            ['type' => 'runtime',
                'area' => 'frontend',
                'package' => 'default',
                'theme' => 'default123',
                'store_id' => '1'],
            'save',
            $this->returnSelf()
        );

        $loader = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface');
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');
        $loader->expects($this->exactly(2))
            ->method('loadIntoProcessor')
            ->withConsecutive(
                ['string_1', $processor, $index],
                ['string_2', $processor, $index]
            )
            ->willReturnSelf()
        ;

        $this->configureLayout($processor, $loader);

        $this->setCurrentStore('default');
        $this->assertSame($this->update, $this->update->loadRuntime());
    }

    public function testItDoesNotOperateWithIndexIfNoRuntimeSourcesAreFound()
    {
        $this->factory->expects($this->never())
            ->method('createInstance');

        $this->assertSame($this->update, $this->update->loadRuntime());
    }

    public function testItDoesNotCreateNormalSourcesIfCacheIsStillValid()
    {
        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $this->configureDesignPackage($designPackage, 'frontend', 'default', 'default123');

        $cache = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CacheInterface');
        $cache->expects($this->once())
            ->method('load')
            ->with('LAYOUT_VALIDITY_'.gethostname().'_frontend_default_default123_1')
            ->willReturn(true);

        $this->update->setCache($cache);

        $index = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $indexParams = [
            'type' => 'normal',
            'area' => 'frontend',
            'package' => 'default',
            'theme' => 'default123',
            'store_id' => '1'
        ];

        $this->configureIndexMethodCall($index, $indexParams, 'load', true);
        $this->configureCreateInstance($index, 'index');

        $loader = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface');
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');

        $loader->expects($this->exactly(2))
            ->method('loadIntoProcessor')
            ->withConsecutive(
                ['handle_one', $processor, $index],
                ['handle_two', $processor, $index]
            )
            ->willReturnSelf()
        ;

        $this->configureLayout($processor, $loader);

        $this->setCurrentStore('default');
        $this->update->addHandle('handle_one');
        $this->assertSame(
            $this->update,
            $this->update->load('handle_two')
        );
    }

    public function testItUpdatesNormalIndexIfLayoutCacheIsNotValid()
    {
        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $this->configureDesignPackage($designPackage, 'frontend', 'default', 'default123');

        $cache = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CacheInterface');
        $cache->expects($this->once())
            ->method('load')
            ->with('LAYOUT_VALIDITY_'.gethostname().'_frontend_default_default123_1')
            ->willReturn(false);

        $cache->expects($this->once())
            ->method('save')
            ->with('LAYOUT_VALIDITY_'.gethostname().'_frontend_default_default123_1', true, 3600)
            ->willReturnSelf();

        $this->update->setCache($cache);

        $designFiles = $this->mockModel('ecomdev_layoutcompiler/layout_update_design_files')
            ->replaceByMock('model');

        $designFiles->expects($this->once())
            ->method('getDesignLayoutFiles')
            ->with($designPackage, 'frontend', 'default', 'default123', '1')
            ->willReturn(['path/to/file1.xml', 'path/to/file2.xml'])
        ;

        $sourceFileOne = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceFileTwo = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');
        $sourceDb = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_SourceInterface');

        $index = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_IndexInterface');
        $indexParams = [
            'type' => 'normal',
            'area' => 'frontend',
            'package' => 'default',
            'theme' => 'default123',
            'store_id' => '1'
        ];

        $this->configureIndexMethodCall($index, $indexParams, 'load', true);
        $this->configureIndexMethodCall($index, $indexParams, 'save', true);
        $this->configureIndexMethodCall($index, [$sourceFileOne, $sourceFileTwo, $sourceDb], 'update', true);
        $this->configureCreateInstance([
            [$index, 'index'],
            [$sourceFileOne, 'layout_source_file', 'path/to/file1.xml'],
            [$sourceFileTwo, 'layout_source_file', 'path/to/file2.xml'],
            [$sourceDb, 'layout_source_database', [
                'area' => 'frontend',
                'package' => 'default',
                'theme' => 'default123',
                'store_id' => '1'
            ]]
        ]);

        $loader = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface');
        $processor = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface');

        $loader->expects($this->once())
            ->method('loadIntoProcessor')
            ->with('handle_one', $processor, $index)
            ->willReturnSelf()
        ;

        $this->configureLayout($processor, $loader);
        $this->setCurrentStore('default');
        $this->assertSame(
            $this->update,
            $this->update->load('handle_one')
        );
    }

    public function testItNeverTriggersAnyOperationOnLoadIfNoHandlesAdded()
    {
        $this->factory->expects($this->never())
            ->method('createInstance');
        $this->assertSame($this->update, $this->update->load());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid layout update handle
     */
    public function testItThrowsAnExceptionIfHandlesIsNotAStringAndNotAnArrayForLoadMethod()
    {
        $this->update->load(0);
    }

    private function configureDesignPackage(
        PHPUnit_Framework_MockObject_MockObject $designPackage, $area, $package, $theme
    )
    {
        $designPackage->expects($this->once())
            ->method('getArea')
            ->willReturn($area);

        $designPackage->expects($this->once())
            ->method('getPackageName')
            ->willReturn($package);

        $designPackage->expects($this->once())
            ->method('getTheme')
            ->with('layout')
            ->willReturn($theme);

        $this->update->setDesignPackage($designPackage);
        return $this;
    }

    private function configureIndexMethodCall(
        PHPUnit_Framework_MockObject_MockObject $index, $parameters, $method, $returnValue
    )
    {
        $methodStub = $index->expects($this->once())
            ->method($method)
            ->with($parameters);

        if ($returnValue instanceof PHPUnit_Framework_MockObject_Stub) {
            $methodStub->will($returnValue);
        } else {
            $methodStub->willReturn($returnValue);
        }

        return $this;
    }

    private function configureCreateInstance($sequence)
    {
        if (!is_array($sequence)) {
            $args = func_get_args();
            $sequence = [$args];
        }

        $withSequence = array();
        $willSequence = array();
        foreach ($sequence as $args) {
            $instance = array_shift($args);
            $withSequence[] = $args;
            $willSequence[] = $instance;
        }

        $method = $this->factory->expects($this->exactly(count($sequence)))
            ->method('createInstance');

        call_user_func_array(array($method, 'withConsecutive'), $withSequence);
        call_user_func_array(array($method, 'willReturnOnConsecutiveCalls'), $willSequence);
        return $this;
    }

    /**
     * Configures layout
     *
     * @param EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface|null $processor
     * @param EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface|null $loader
     * @return EcomDev_LayoutCompiler_Contract_LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function configureLayout($processor = null, $loader = null)
    {
        $layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
        $this->update->setLayout($layout);

        if ($processor) {
            $layout->expects($this->once())
                ->method('getProcessor')
                ->willReturn($processor);
        }

        if ($loader) {
            $layout->expects($this->once())
                ->method('getLoader')
                ->willReturn($loader);
        }

        return $layout;
    }
}
