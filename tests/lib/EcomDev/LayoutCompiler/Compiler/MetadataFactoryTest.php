<?php

use EcomDev_LayoutCompiler_Compiler_MetadataFactory as MetadataFactory;

class EcomDev_LayoutCompiler_Compiler_MetadataFactoryTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait {
        createSource as private traitCreateSource;
    }

    /**
     * Factory instance
     * 
     * @var MetadataFactory
     */
    private $factory;
    
    protected function setUp()
    {
        $this->factory = new MetadataFactory();
    }
    
    public function testItReturnsAnNewInstanceOfMetadataForASource()
    {
        $source = $this->createSource(2, 'id_one', 'checksum_one');
        
        $this->factory->setSavePath('test/save/path');
        $metadataOne = $this->factory->createFromSource($source, array('item1'));
        $metadataTwo = $this->factory->createFromSource($source, array('item1'));
        
        $this->assertInstanceOf('EcomDev_LayoutCompiler_Compiler_Metadata', $metadataOne);
        $this->assertEquals($metadataOne, $metadataTwo);
        $this->assertNotSame($metadataOne, $metadataTwo);
        $this->assertSame('id_one', $metadataOne->getId());
        $this->assertSame('checksum_one', $metadataOne->getChecksum());
        $this->assertSame(array('item1'), $metadataOne->getHandles());
        $this->assertSame('test/save/path', $metadataOne->getSavePath());
        
    }

    /**
     * Creates a new source instance
     * 
     * @param int $times
     * @param string $id
     * @param string $checksum
     * @return EcomDev_LayoutCompiler_Contract_Layout_SourceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createSource($times, $id, $checksum)
    {
        $source = $this->traitCreateSource(false);
        $source->expects($this->exactly($times))
            ->method('getId')
            ->willReturn($id);

        $source->expects($this->exactly($times))
            ->method('getChecksum')
            ->willReturn($checksum);

        return $source;
    }
}
