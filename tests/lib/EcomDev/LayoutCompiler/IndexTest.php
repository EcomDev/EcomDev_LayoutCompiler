<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;

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
    
    protected function setUp()
    {
        $this->index = new EcomDev_LayoutCompiler_Index();
        $this->compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');
        $this->layout = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_LayoutInterface');
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

        $this->layout->expects($this->once())
            ->method('getCompiler')
            ->willReturn($this->compiler);
        
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