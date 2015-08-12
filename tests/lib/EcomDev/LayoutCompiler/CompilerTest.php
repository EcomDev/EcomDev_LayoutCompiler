<?php

use EcomDev_LayoutCompiler_Compiler as Compiler;
use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamDirectory as StreamDirectory;

class EcomDev_LayoutCompiler_CompilerTest 
    extends PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    use EcomDev_LayoutCompiler_HelperTestTrait;
    
    /**
     * Metadata factory
     * 
     * @var EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * Stream directory
     * 
     * @var StreamDirectory
     */
    private $fileSystem;

    /**
     * 
     */
    protected function setUp()
    {
        $this->metadataFactory = $this->getMockForAbstractClass($this->metadataFactoryInterface);
        $this->fileSystem = Stream::setup();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Metadata factory is not set in compiler option "metadata_factory"
     */
    public function testItRisesAnExceptionIfMetadataFactoryIsNotSet()
    {
        new Compiler();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Metadata factory of class "stdClass" does not implement "EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface"
     */
    public function testItRisesAnExceptionIfMetadataFactoryDoesNotImplementRequiredInterface()
    {
        new Compiler(array('metadata_factory' => new stdClass()));
    }
    
    private function getCompilerOptions()
    {
        return array('metadata_factory' => $this->metadataFactory);
    }
    
    public function testItStoresAssignedMetadataFactory()
    {
        $compiler = new Compiler($this->getCompilerOptions());
        $this->assertSame($this->metadataFactory, $compiler->getMetadataFactory());
    }
    
    public function testItIsPossibleToSetSavePathFromOptions()
    {
        $compiler = new Compiler($this->getCompilerOptions() + array('save_path' => 'path/test'));
        $this->assertSame('path/test', $compiler->getSavePath());
    }
    
    public function testItPassesSavePathToMetadataFactory()
    {
        $compiler = new Compiler($this->getCompilerOptions());
        
        $this->metadataFactory->expects($this->once())
            ->method('setSavePath')
            ->with('path/to/save')
            ->willReturnSelf();
        
        $this->assertSame($compiler, $compiler->setSavePath('path/to/save'));
    }

    public function testItCompilesAStringSource()
    {
        $parser = $this->createParser('new Node()');
        
        $source = $this->createSource(array(
            'item1' => array('node' => array_fill(0, 4, array())), // 4 node items
            'item2' => array('node' => array_fill(0, 2, array())), // 2 node items
            'item3' => array('node2' => array())
        ));
                
        $handleFilePaths = array(
            'item1' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item1.php'),
            'item2' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item2.php'),
            'item3' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item3.php')
        );
        
        $metadata = $this->createMetadata(
            array_diff_key($handleFilePaths, array('item3' => null))
        );

        $this->metadataFactory->expects($this->once())
            ->method('createFromSource')
            ->with($this->identicalTo($source), array('item1', 'item2'))
            ->willReturn($metadata);
        
        $compiler = new Compiler(
            $this->getCompilerOptions() + array('parsers' => array('node' => $parser))
        );

        $this->assertSame($metadata, $compiler->compile($source));
        
        $this->assertStringEqualsFile(
            $handleFilePaths['item1'], 
            "<?php \$this->addItem(new Node());\n\$this->addItem(new Node());\n\$this->addItem(new Node());\n\$this->addItem(new Node());"
        );
        
        $this->assertStringEqualsFile(
            $handleFilePaths['item2'],
            "<?php \$this->addItem(new Node());\n\$this->addItem(new Node());"
        );
        
        $this->assertFileNotExists($handleFilePaths['item3']);
    }

    public function testItAllowsToCompilesAnExpressionSource()
    {
        $parser = $this->createParser(
            new EcomDev_LayoutCompiler_Exporter_Expression('$this->newValue(new Node())')
        );

        $source = $this->createSource(array(
            'item1' => array('node' => array_fill(0, 4, array())), // 4 node items
            'item2' => array('node' => array_fill(0, 2, array())), // 2 node items
            'item3' => array('node2' => array())
        ));

        $handleFilePaths = array(
            'item1' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item1.php'),
            'item2' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item2.php'),
            'item3' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item3.php')
        );

        $metadata = $this->createMetadata(
            array_diff_key($handleFilePaths, array('item3' => null))
        );

        $this->metadataFactory->expects($this->once())
            ->method('createFromSource')
            ->with($this->identicalTo($source), array('item1', 'item2'))
            ->willReturn($metadata);

        $compiler = new Compiler(
            $this->getCompilerOptions() + array('parsers' => array('node' => $parser))
        );

        $this->assertSame($metadata, $compiler->compile($source));

        $this->assertStringEqualsFile(
            $handleFilePaths['item1'],
            "<?php \$this->newValue(new Node());\n\$this->newValue(new Node());\n\$this->newValue(new Node());\n\$this->newValue(new Node());"
        );

        $this->assertStringEqualsFile(
            $handleFilePaths['item2'],
            "<?php \$this->newValue(new Node());\n\$this->newValue(new Node());"
        );

        $this->assertFileNotExists($handleFilePaths['item3']);
    }

    public function testItDoesNotCompileASourceIfExistingMetadataIsValid()
    {
        $source = $this->createSource(false);
        $metadata = $this->createMetadata();
        
        $metadata->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($source))
            ->willReturn(true);
        
        $this->metadataFactory->expects($this->never())
            ->method('createFromSource');

        $compiler = new Compiler($this->getCompilerOptions());
        $this->assertSame($metadata, $compiler->compile($source, $metadata));
    }

    public function testItRemovesAnOldSourceFileBeforeReCompiling()
    {
        $parser = $this->createParser('new Node()');
        // Pre-filling item that should be deleted
        mkdir(Stream::url($this->fileSystem->path() . '/compiled'));
        file_put_contents(Stream::url($this->fileSystem->path() . '/compiled/item_id_item2.php'), '<?php test file');
        
        // Source contains only one handle, so no additional handles should be created
        $source = $this->createSource(array('item1' => array('node' => array_fill(0, 4, array()))));

        $handleFilePaths = array(
            'item1' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item1.php'),
            'item2' => Stream::url($this->fileSystem->path() . '/compiled/item_id_item2.php')
        );
        
        $metadata = $this->createMetadata($handleFilePaths, true);
        $newMetadata = $this->createMetadata(array_diff_key($handleFilePaths, array('item2' => null)), true);

        $this->metadataFactory->expects($this->once())
            ->method('createFromSource')
            ->with($this->identicalTo($source), array('item1'))
            ->willReturn($newMetadata);

        $compiler = new Compiler(
            $this->getCompilerOptions() + array('parsers' => array('node' => $parser))
        );

        $this->assertSame($newMetadata, $compiler->compile($source, $metadata));

        $this->assertStringEqualsFile(
            $handleFilePaths['item1'],
            "<?php \$this->addItem(new Node());\n\$this->addItem(new Node());\n\$this->addItem(new Node());\n\$this->addItem(new Node());"
        );

        $this->assertFileNotExists($handleFilePaths['item2']);        
    }
    
    public function testItIsPossibleToManipulateWithParsers()
    {
        list($parserOne, $parserTwo, $parserThree) = array(
            $this->createParser(), $this->createParser(), $this->createParser()
        );
        
        $compiler = new Compiler($this->getCompilerOptions());
        $compiler->setParser('node1', $parserOne);
        $compiler->setParser('node3', $parserThree);
        
        $this->assertSame(
            array('node1' => $parserOne, 'node3' => $parserThree),
            $compiler->getParsers()
        );
        
        $this->assertSame(
            $compiler, 
            $compiler->setParser('node2', $parserTwo)
        );

        $this->assertSame(
            array('node1' => $parserOne, 'node3' => $parserThree, 'node2' => $parserTwo),
            $compiler->getParsers()
        );

        $this->assertSame(
            $compiler, $compiler->removeParser('node3')
        );

        $this->assertSame(
            array('node1' => $parserOne, 'node2' => $parserTwo), 
            $compiler->getParsers()
        );
    }

    /**
     * @param SimpleXMLElement $element
     * @param string[] $expectedResult
     * @param string|null $parentIdentifier
     * @dataProvider dataProviderForParseElement
     */
    public function testItParsesElements(SimpleXMLElement $element,
                                         array $expectedResult,
                                         $parentIdentifier = null)
    {
        $parsers = array(
            'node1' => $this->createParser($this->returnCallback(
                function(SimpleXMLElement $element, Compiler $compiler, $parentIdentifier) {
                    return 'Node1' . ($parentIdentifier !== null ? sprintf('(%s)', $parentIdentifier) : '');
                }
            )),
            'node2' => $this->createParser($this->returnCallback(
                function(SimpleXMLElement $element, Compiler $compiler, $parentIdentifier, array $parentIdentifiers = array()) {
                    if ($parentIdentifier) {
                        $parentIdentifiers[] = $parentIdentifier;
                    }
                    
                    return $compiler->parseElements($element, (string)$element->attributes()->identifier, $parentIdentifiers);
                }
            )),
            'node3' => $this->createParser($this->returnValue('Node3')),
            'node4' => $this->createParser($this->returnValue(false)),
            'node5' => $this->createParser($this->returnCallback(
                function(SimpleXMLElement $element, Compiler $compiler, $parentIdentifier) {
                    return $compiler->parseElements($element, $parentIdentifier); // Just resets data back
                }
            )),
            'node6' => $this->createParser($this->returnCallback(
                function(SimpleXMLElement $element, Compiler $compiler, $blockIdentifier, array $parentIdentifiers = array()) {
                    return sprintf('Node6(%s, [%s])', $blockIdentifier, implode(', ', $parentIdentifiers));
                }
            )),
        );

        $compiler = new Compiler($this->getCompilerOptions() + array('parsers' => $parsers));
        $this->assertSame(
            $expectedResult,
            $compiler->parseElements($element, $parentIdentifier)
        );
    }

    public function dataProviderForParseElement()
    {
        $data = array(
            array(
                $this->createXmlElement('general_data', array(
                    'node1' => array_fill(0, 3, array('key' => 'value')),
                    'node2' => array('key' => 'value'),
                    'node3' => array('key' => 'value')
                )),
                array('Node1', 'Node1', 'Node1', 'Node3')
            ),
            array(
                $this->createXmlElement('general_data', array(
                    'node2' => array(
                        '@identifier' => 'one',
                        'node1' => array(),
                        'node2' => array(
                            '@identifier' => 'two',
                            'node1' => array_fill(0, 2, array('key' => 'value'))
                        )
                    ),
                    'node3' => array('key' => 'value')
                )),
                array('Node1(one)', 'Node1(two)', 'Node1(two)', 'Node3')
            ),
            array(
                $this->createXmlElement('general_data', array(
                        'node4' => array()
                )),
                array()
            ),
            array(
                $this->createXmlElement('general_data', array(
                    'node1' => array_fill(0, 3, array('key' => 'value'))
                )),
                array('Node1(specific)', 'Node1(specific)', 'Node1(specific)'),
                'specific'
            ),
            array(
                $this->createXmlElement('general_data', array(
                    'node2' => array(
                        array(
                            '@identifier' => 'one',
                            'node2' => array(
                                '@identifier' => 'two',
                                'node5' => array(
                                    'node2' => array(
                                        '@identifier' => 'three',
                                        'node6' => array(
                                            'key'=> 'value'
                                        )
                                    )
                                ),
                                'node6' => array(
                                    'key'=> 'value'
                                ),
                                'node2' => array(
                                    '@identifier' => 'four',
                                    'node6' => array('key' => 'value')
                                )
                            )
                        ),
                        array(
                            '@identifier' => 'five',
                            'node6' => array('key' => 'value')
                        ),

                    )
                )),
                array('Node6(three, [two])', 'Node6(two, [one])', 'Node6(four, [one, two])', 'Node6(five, [])')
            ),
        );
        
        return $data;
    }

    
}
