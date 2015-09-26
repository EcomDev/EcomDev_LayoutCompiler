<?php

use EcomDev_LayoutCompiler_Compiler_Metadata as Metadata;

class EcomDev_LayoutCompiler_Compiler_MetadataTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    /**
     * @var Metadata
     */
    private $metadata;
    
    protected function setUp()
    {
        $this->metadata = new Metadata(array('handle1', 'handle2'), 'my_id', 'checksum', 'base/path');
    }
    
    public function testItReturnsHandlesPassedInConstructor()
    {
        $this->assertSame(array('handle1', 'handle2'), $this->metadata->getHandles());
    }
    
    public function testItReturnsIdPassedInConstructor()
    {
        $this->assertSame('my_id', $this->metadata->getId());
    }
    
    public function testItReturnsChecksumPassedInConstructor()
    {
        $this->assertSame('checksum', $this->metadata->getChecksum());
    }
    
    public function testItReturnsSavePathPassedInConstructor()
    {
        $this->assertSame('base/path', $this->metadata->getSavePath());
    }
    
    public function testItUsesSavePathAndIdAsPrefixForHandlePath()
    {
        $this->assertSame('base/path/my_id_handle1.php', $this->metadata->getHandlePath('handle1'));
    }
    
    public function testItInvalidatesMetadataIfSourceChecksumIsDifferent()
    {
        $source = $this->createSource(false);
        $source->expects($this->never())
            ->method('getId');
        
        $source->expects($this->once())
            ->method('getChecksum')
            ->willReturn('different_checksum');
        
        $this->assertFalse($this->metadata->validate($source));
    }

    /**
     * @param array $state
     * @dataProvider dataProviderMetadataState
     */
    public function testItCreatesAnObjectWithPassedStateArray(array $state)
    {
        $metadata = Metadata::__set_state($state);
        $this->assertInstanceOf(
            'EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface',
            $metadata
        );
        $this->assertSame($state['id'], $metadata->getId());
        $this->assertSame($state['checksum'], $metadata->getChecksum());
        $this->assertSame($state['handles'], $metadata->getHandles());
        $this->assertSame($state['savePath'], $metadata->getSavePath());
    }
    
    public function dataProviderMetadataState()
    {
        return array(
            array(
                array('id' => 'test_id1', 
                      'checksum' => 'test_checksum1', 
                      'handles' => array('test_item1'), 
                      'savePath' => 'test_path1')
            ),
            array(
                array('id' => 'test_id2',
                      'checksum' => 'test_checksum2',
                      'handles' => array('test_item2'),  
                      'savePath' => 'test_path2')
            ),
            array(
                array('id' => 'test_id3',
                      'checksum' => 'test_checksum3', 
                      'handles' => array('test_item3'), 
                      'savePath' => 'test_path3')
            ),
            array(
                array('id' => 'test_id4', 
                      'checksum' => 'test_checksum4', 
                      'handles' => array('test_item4'),
                      'savePath' => 'test_path4')
            )
        );
    }
}
