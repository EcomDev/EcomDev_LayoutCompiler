<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamDirectory as StreamDirectory;
use EcomDev_LayoutCompiler_Compiler_Metadata as Metadata;


class EcomDev_LayoutCompiler_IndexTest  
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;
    
    /**
     * @var EcomDev_LayoutCompiler_Index
     */
    private $index;

    /**
     * @var EcomDev_LayoutCompiler_Contract_CompilerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $compiler;

    /**
     * @var EcomDev_LayoutCompiler_Contract_LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var StreamDirectory
     */
    private $virtualDirectory;
    
    protected function setUp()
    {
        $this->index = new EcomDev_LayoutCompiler_Index();
        $this->compiler = $this->createCompiler();
        $this->layout = $this->createLayout($this->compiler);
        $this->virtualDirectory = Stream::setup();
    }
    
    public function testItAddsMetadataForInfoSystem()
    {   
        $metadataList = $this->stubMetadataObjects(array(
            'id_one' => array('handle_one', 'handle_two', 'handle_three'),
            'id_two' => array('handle_two'),
        ));
        
        $metadataListOne = array('id_one' => $metadataList['id_one']);
        $this->assertAttributeSame($metadataList, 'metadata', $this->index);
        $this->assertAttributeSame(
            array(
                'handle_one' => $metadataListOne, 
                'handle_two' => $metadataList, 
                'handle_three' => $metadataListOne
            ),
            'metadataByHandle',
            $this->index
        );
    }

    public function testItBuildsStringBasedOnParametersAndAlwaysSortsThemByKey()
    {
        $this->assertSame(
            'index_keya_value2_keyb_value1', 
            $this->index->getIndexIdentifier(array('keya' => 'value2', 'keyb' => 'value1'))
        );
        
        $this->assertSame(
            'index_keya_value2_keyb_value1', 
            $this->index->getIndexIdentifier(array('keyb' => 'value1', 'keya' => 'value2'))
        );
    }
    
    public function testItOnlyConvertsStringThatContainNonLatinLettersToHexValue()
    {
        $this->assertSame(
            'index_testx00x0A_test_value_value_x01x02x04adfgAH13567',
            $this->index->getIndexIdentifier(array("test\0\n" => 'test_value', 'value' => "\x01\x02\x04adfgAH13567"))
        );
    }
    
    public function testItReturnsFalseIfFailsToLoadNonExistingIndexFile()
    {
        $this->index->setSavePath($this->virtualDirectory->url());
        
        $this->assertFalse($this->index->load(array('param1' => 'value1')));
    }

    /**
     * @param $fileContent
     * @param $expectedMetadataObjects
     * @param $expectedMetadataHandleObjects
     * 
     * @dataProvider dataProviderMetadataObjectsFromFile
     */
    public function testItLoadsMetaDataObjectsFromFile(
        $fileContent, $expectedMetadataObjects, $expectedMetadataHandleObjects
    )
    {
        Stream::create(array('index_test_file.php' => $fileContent), $this->virtualDirectory);
        $this->index->setSavePath($this->virtualDirectory->url());
        $this->assertTrue($this->index->load(array('test' => 'file')));
        
        $this->assertAttributeEquals($expectedMetadataObjects, 'metadata', $this->index);
        $this->assertAttributeEquals($expectedMetadataHandleObjects, 'metadataByHandle', $this->index);
    }
    
    public function dataProviderMetadataObjectsFromFile()
    {
        $data = array(
            'three_items' => array(
                array(
                    array(
                        'handles' => array('handle_one'),
                        'id' => 'item_one',
                        'checksum' => '1234563127',
                        'savePath' => '/some/path/somewhere'
                    ),
                    array(
                        'handles' => array('handle_two'),
                        'id' => 'item_two',
                        'checksum' => '12345612347',
                        'savePath' => '/some/path/somewhere'
                    ),
                    array(
                        'handles' => array('handle_three'),
                        'id' => 'item_three',
                        'checksum' => '123456123123123127',
                        'savePath' => '/some/path/somewhere'
                    )
                ),
                array(
                    'handle_one' => array(
                        'item_one'
                    ),
                    'handle_two' => array(
                        'item_two'
                    ),
                    'handle_three' => array(
                        'item_three'
                    )
                )
            ),
            'two_items' => array(
                array(
                    array(
                        'handles' => array('handle_one'),
                        'id' => 'item_one',
                        'checksum' => '1234563127',
                        'savePath' => '/some/path/somewhere'
                    ),
                    array(
                        'handles' => array('handle_three', 'handle_two'),
                        'id' => 'item_three',
                        'checksum' => '123456123123123127',
                        'savePath' => '/some/path/somewhere'
                    )
                ),
                array(
                    'handle_one' => array(
                        'item_one'
                    ),
                    'handle_three' => array(
                        'item_three'
                    ),
                    'handle_two' => array(
                        'item_three'
                    )
                )
            )
        );
        
        $className = 'EcomDev_LayoutCompiler_Compiler_Metadata';
        $dataSet = array();
        foreach ($data as $setName => $set) {
            $file = "<?php \n";
            list($objectsInfo, $handleInfo) = $set;
            $objects = array();
            $handles = array();
            foreach ($objectsInfo as $objectInfo) {
                $file .= sprintf("\$this->addMetadata(%s::__set_state(%s));\n", $className, var_export($objectInfo, true));
                $object = Metadata::__set_state($objectInfo);
                $objects[$object->getId()] = $object;
            }
            
            foreach ($handleInfo as $handleName => $objectIds) {
                foreach ($objectIds as $objectId) {
                    if (isset($objects[$objectId])) {
                        $handles[$handleName][$objectId] = $objects[$objectId];
                    }
                }
            }
            
            $dataSet[$setName] = array($file, $objects, $handles);
        }
        
        return $dataSet;
    }

    /**
     * @param EcomDev_LayoutCompiler_Compiler_Metadata[] $metadataObjects
     * @param string $expectedFileContent
     *
     * @dataProvider dataProviderMetadataObjectsToFile
     */
    public function testItSaveMetaDataObjectsToFile(
        $metadataObjects, $expectedFileContent
    )
    {
        foreach ($metadataObjects as $metadata) {
            $this->index->addMetadata($metadata);
        }
        
        $path = $this->virtualDirectory->path() . '/somepath';
        
        $this->index->setSavePath(Stream::url($path));
        $this->assertSame(
            $this->index, 
            $this->index->save(array('test' => 'file'))
        );
        
        $this->assertStringEqualsFile(
            Stream::url($path . '/index_test_file.php'),
            $expectedFileContent
        );
    }

    /**
     * Data provider for save operation test
     * 
     * @return array[]
     */
    public function dataProviderMetadataObjectsToFile()
    {
        $fileLine = '$this->addMetadata(%s);';
        $file = "<?php \n%s";
        
        return array(
            'item_one' => array(
                array(
                    $metadataOne = Metadata::__set_state(array(
                        'handles' => array('item1', 'item2'),
                        'id' => 'item_one',
                        'checksum' => 'checksum_one',
                        'savePath' => '/some/path/one'
                    )),
                    $metadataTwo = Metadata::__set_state(array(
                        'handles' => array('item2', 'item3'),
                        'id' => 'item_two',
                        'checksum' => 'checksum_two',
                        'savePath' => '/some/path/two'
                    ))
                ), 
                sprintf(
                    $file, 
                    implode("\n", array(
                        sprintf(
                            $fileLine,  
                            var_export($metadataOne, true)
                        ),
                        sprintf(
                            $fileLine, 
                            var_export($metadataTwo, true)
                        )
                    ))
                )
            ),
            'item_two' => array(
                array(
                    $metadataOne = Metadata::__set_state(array(
                        'handles' => array('item3', 'item4'),
                        'id' => 'item_three',
                        'checksum' => 'checksum_three',
                        'savePath' => '/some/path/one'
                    ))
                ),
                sprintf(
                    $file,
                    sprintf(
                        $fileLine,
                        var_export(
                            $metadataOne,
                            true
                        )
                    )
                )
            ),
            'empty' => array(
                array(),
                sprintf($file, '')
            )
        );
    }

    public function testItReturnsHandleIncludePathsFromMetadataObjects()
    {
        $this->stubMetadataObjects(array(
            'id_one' => array('handle_one' => 'file1.php', 'handle_two' => 'file2.php', 'handle_three' => 'file3.php'),
            'id_two' => array('handle_two' => 'file4.php'),
        ));

        $this->assertSame(array('file1.php'), $this->index->getHandleIncludes('handle_one'));
        $this->assertSame(array('file2.php', 'file4.php'), $this->index->getHandleIncludes('handle_two'));
        $this->assertSame(array('file3.php'), $this->index->getHandleIncludes('handle_three'));
        $this->assertSame(array(), $this->index->getHandleIncludes('handle_four'));
    }
    
    public function testItUpdatesMetadataObjectsFromSource()
    {
        $sources = $this->stubSources(array('id_one', 'id_two', 'id_three', 'id_four', 'id_five', 'id_seven'));
        $metadata = $this->stubMetadataObjects(
            array(
                'id_one' => array('handle_one', 'handle_two', 'handle_three'),
                'id_two' => array('handle_two', 'handle_three', 'handle_five', 'handle_six'),
                'id_four' => array('handle_four'),
                'id_five' => array('handle_five'),
                'id_six' => array('handle_six')
            ),
            array(2, 1, 1, 2, 1)
        );
        
        $newMetadata = $this->stubMetadataObjects(
            array(
                'id_two' => array('handle_two'),
                'id_three' => array('handle_three'),
                'id_four' => array('handle_four', 'handle_five'),
            ),
            1, 
            false
        );
        
        $expectedMetadata = array(
            'id_one' => $metadata['id_one'],
            'id_two' => $newMetadata['id_two'],
            'id_three' => $newMetadata['id_three'],
            'id_four' => $newMetadata['id_four'],
            'id_five' => $metadata['id_five']
        );
        
        $expectedMetadataHandles = array(
            'handle_one' => array('id_one' => $expectedMetadata['id_one']),
            'handle_two' => array('id_one' => $expectedMetadata['id_one'], 'id_two' => $expectedMetadata['id_two']),
            'handle_three' => array('id_one' => $expectedMetadata['id_one'], 'id_three' => $expectedMetadata['id_three']),
            'handle_four' => array('id_four' => $expectedMetadata['id_four']),
            'handle_five' => array('id_four' => $expectedMetadata['id_four'], 'id_five' => $expectedMetadata['id_five'])
        );
        
        $testException = new RuntimeException('something wrong is going to happen');
        
        $this->compiler->expects($this->exactly(6))
            ->method('compile')
            ->withConsecutive(
                array($sources['id_one'], $metadata['id_one']),
                array($sources['id_two'], $metadata['id_two']),
                array($sources['id_three'], null),
                array($sources['id_four'], $metadata['id_four']),
                array($sources['id_five'], $metadata['id_five']),
                array($sources['id_seven'], null)
            )
            ->willReturnOnConsecutiveCalls(
                $metadata['id_one'],
                $newMetadata['id_two'],
                $newMetadata['id_three'],
                $newMetadata['id_four'],
                $metadata['id_five'],
                $this->throwException($testException)
            );
        ;
        
        $errorProcessor = $this->createErrorProcessor($this->index);
        $errorProcessor->expects($this->once())
            ->method('processException')
            ->with($testException)
            ->willReturnSelf();
        
        
        $this->index->setLayout($this->layout);
        $this->index->update($sources);
        
        $this->assertAttributeSame($expectedMetadata, 'metadata', $this->index);
        $this->assertAttributeSame($expectedMetadataHandles, 'metadataByHandle', $this->index);
        
    }

    /**
     * @param array $metadataInfo
     * @param int|int[] $timesIdCall
     * @param bool $add
     * @return array
     */
    private function stubMetadataObjects(array $metadataInfo, $timesIdCall = 1, $add = true)
    {
        $objects = array();
        foreach ($metadataInfo as $id => $handles) {
            if (is_int(key($handles))) {
                $handles = array_combine($handles, $handles);
            }
            
            if (is_array($timesIdCall)) {
                $countIdCall = array_shift($timesIdCall);
            } else {
                $countIdCall = $timesIdCall;
            }
            
            $metadata = $this->createMetadata($handles, $countIdCall, false);
                        
            $metadata->expects($this->exactly($countIdCall))
                ->method('getId')
                ->willReturn($id);
            
            if ($add) {
                $this->assertSame($this->index, $this->index->addMetadata($metadata));
            }
            
            $objects[$id] = $metadata; 
        }
        
        return $objects;
    }

    /**
     * @param string[] $idList
     * @return SourceInterface[]
     */
    private function stubSources($idList) 
    {
        $result = array();
        foreach ($idList as $id) {
            $source = $this->createSource(false);
            $source->expects($this->any())
                ->method('getId')
                ->willReturn($id);
            
            $result[$id] = $source;
        }
        
        return $result;
    }
}