<?php

use EcomDev_LayoutCompiler_Layout_Source_File as SourceFile;
use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamDirectory as StreamDirectory;

class EcomDev_LayoutCompiler_Layout_Source_FileTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var SourceFile
     */
    private $source;

    /**
     * @var StreamDirectory
     */
    private $fileSystem;

    protected function setUp()
    {
        $this->fileSystem = Stream::setup();
        $annotations = $this->getAnnotations()['method'];
        
        if (isset($annotations['sourceFile'])) {
            $fileName = current($annotations['sourceFile']);
            $this->source = new SourceFile(Stream::url($this->fileSystem->path() . '/' . $fileName));
        }
    }

    /**
     * @sourceFile non_existing/layout_file.xml
     * @expectedException RuntimeException
     * @expectedExceptionMessage File for source does not exists at path "vfs://root/non_existing/layout_file.xml"
     */
    public function testItRisesAnExceptionIfFileDoesNotExists()
    {
        $this->source->validate();
    }

    /**
     * @sourceFile non_readable/layout_file.xml
     * @expectedException RuntimeException
     * @expectedExceptionMessage File for source is not readable at path "vfs://root/non_readable/layout_file.xml"
     */
    public function testItRisesAnExceptionIfFileIsNotReadable()
    {
        $this->prepareNonReadableFile();
        $this->source->validate();
    }

    /**
     * @sourceFile non_readable/layout_file.xml
     * @expectedException RuntimeException
     * @expectedExceptionMessage File for source is not readable at path "vfs://root/non_readable/layout_file.xml"
     */
    public function testItRisesExceptionOnLoadIfFileIsNotReadable()
    {
        $this->prepareNonReadableFile();
        $this->source->load();
    }

    /**
     * @sourceFile non_readable/layout_file.xml
     * @expectedException RuntimeException
     * @expectedExceptionMessage File for source is not readable at path "vfs://root/non_readable/layout_file.xml"
     */
    public function testItRisesExceptionOnChecksumIfFileIsNotReadable()
    {
        $this->prepareNonReadableFile();
        $this->source->getChecksum();
    }

    /**
     * @sourceFile existing/layout_file.xml
     */
    public function testItWorksCorrectlyIfFileExistsAndReadable()
    {
        $this->prepareLayoutFile();
        $this->assertSame($this->source, $this->source->validate());
    }

    public function testItReturnsFullPathToFileAsOriginalFileName()
    {
        $this->source = new SourceFile('test/file/path.xml');
        $this->assertSame('test/file/path.xml', $this->source->getOriginalPath());
    }
    
    public function testItReturnsIdBasedOnFilePath()
    {
        $this->source = new SourceFile('test/path/design/base/default/layout/directory/path.xml');
        $this->assertSame(
            'file_default_layout_directory_path_b84d60877e7368d930b5b4a3e6fd7887', 
            $this->source->getId()
        );

        $this->source = new SourceFile('test/path/design/base/default/layout/path.xml');
        $this->assertSame(
            'file_base_default_layout_path_364208471de9b1239bcecec1db29d0b8',
            $this->source->getId()
        );
    }

    /**
     * @sourceFile existing/layout_file.xml
     */
    public function testItReturnsFileChecksumBasedOnItsContent()
    {
        $this->prepareLayoutFile("<layout><some><node></node></some></layout>");
        
        $this->assertSame(
            '229799e7237217164bb4f82c3b835b16',
            $this->source->getChecksum()
        );
    }

    /**
     * @sourceFile existing/layout_file.xml
     * @expectedException RuntimeException
     * @expectedExceptionMessage File "vfs://root/existing/layout_file.xml" has a malformed xml structure:
     */
    public function testItThrowsRuntimeErrorForMalformedXmlOnLoad()
    {
        $this->prepareLayoutFile("<layout><some>node></node></some></layout>");
        $this->source->load();
    }

    /**
     * @sourceFile existing/layout_file.xml
     */
    public function testItLoadsFileXmlStructureAndReturnsArrayOfFirstLevelElements()
    {
        $expectedElements = array(
            'handle_name_one' => '<handle_name_one><block_one /><block_one /></handle_name_one>',
            'handle_name_two' => '<handle_name_two><block_two /><block_two /></handle_name_two>',
        );
        
        $this->prepareLayoutFile(sprintf(
            '<layout>%s%s</layout>',
            $expectedElements['handle_name_one'],
            $expectedElements['handle_name_two']
        ));
        
        $actualElements = $this->source->load();
        
        $this->assertSame(array_keys($expectedElements), array_keys($actualElements));
        
        foreach ($expectedElements as $key => $element) {
            $this->assertInstanceOf('SimpleXmlElement', $actualElements[$key]);
            $this->assertXmlStringEqualsXmlString(
                (new SimpleXMLElement($element))->asXML(),
                $actualElements[$key]->asXML()
            );
        }
    }

    /**
     * @sourceFile existing/layout_file.xml
     */
    public function testItWorksWithMultipleSameHandleNamesInOneFile()
    {
        $expectedElements = array(
            'handle_name_one' => '<handle_name_one><block_one /><block_one /><block_three item="1"/><block_five item="3"/></handle_name_one>',
            'handle_name_two' => '<handle_name_two><block_two /><block_two /></handle_name_two>',
        );

        $this->prepareLayoutFile(sprintf(
            '<layout>%s%s%s%s</layout>',
            '<handle_name_one><block_one /><block_one /></handle_name_one>',
            $expectedElements['handle_name_two'],
            '<handle_name_one><block_three item="1"/></handle_name_one>',
            '<handle_name_one><block_five item="3"/></handle_name_one>'
        ));

        $actualElements = $this->source->load();

        $this->assertSame(array_keys($expectedElements), array_keys($actualElements));

        foreach ($expectedElements as $key => $element) {
            $this->assertInstanceOf('SimpleXmlElement', $actualElements[$key]);
            $this->assertXmlStringEqualsXmlString(
                (new SimpleXMLElement($element))->asXML(),
                $actualElements[$key]->asXML()
            );
        }
    }

    /**
     * Prepares a non readable file
     * 
     * @return $this
     */
    private function prepareNonReadableFile()
    {
        $this->prepareLayoutFile('<layout></layout>', 'non_readable', 'layout_file.xml');
        $this->fileSystem->getChild('non_readable')->getChild('layout_file.xml')->chmod(0);
        return $this;
    }

    /**
     * Prepares layout file in a directory
     * 
     * @param string $content
     * @param string $directory
     * @param string $fileName
     * @return $this
     */
    private function prepareLayoutFile($content = '<layout></layout>', 
                                       $directory = 'existing', $fileName = 'layout_file.xml')
    {
        Stream::create(
            array(
                $directory => array(
                    $fileName => "<?xml version=\"1.0\"?>\n$content"
                )
            ),
            $this->fileSystem
        );
        
        return $this;
    }
}
