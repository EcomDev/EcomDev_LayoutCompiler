<?php

use EcomDev_LayoutCompiler_Compiler_AbstractParser as AbstractParser;

class EcomDev_LayoutCompiler_Compiler_AbstractParserTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * Parser that is under test
     * 
     * @var AbstractParser
     */
    protected $parser;
    
    protected function setUp()
    {
        $this->parser = $this
            ->getMockForAbstractClass('EcomDev_LayoutCompiler_Compiler_AbstractParser');
    }
    
    public function testItIsPossibleToSpecifyAClassNameForParserOutPut()
    {
        $itemClass = $this->createItemClass();
        
        $this->assertSame(
            $this->parser,
            $this->parser->setClassName($itemClass)
        );
        
        $this->assertSame(
            $itemClass,
            $this->parser->getClassName()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class "stdClass" should implement EcomDev_LayoutCompiler_Contract_Layout_ItemInterface
     */
    public function testItRisesAnExceptionIfSpecifiedClassNameIsNotImplementingItemInterface()
    {
        $this->parser->setClassName('stdClass');
    }
    
    public function testItIsPossibleToSetExporter()
    {
        $exporter = $this->createExporter();
        
        $this->assertSame($this->parser, $this->parser->setExporter($exporter));
        $this->assertSame($exporter, $this->parser->getExporter());
    }

    public function testItGeneratesClassStatementFromProvidedArguments()
    {
        $itemClassName = $this->createItemClass();
        $exporter = $this->createExporter();
        
        $exporter->expects($this->exactly(3))
            ->method('export')
            ->withConsecutive(
                array('new Object'),
                array(array('one' => 'two')),
                array(1)
            )
            ->willReturnOnConsecutiveCalls(
                'new Object',
                "array('one' => 'two')",
                '1'
            );
        
        $this->parser->setClassName($itemClassName);
        $this->parser->setExporter($exporter);
        
        $expectedStatement = sprintf(
            "new %s(new Object, array('one' => 'two'), 1)",
            $itemClassName
        );
        
        $this->assertSame(
            $expectedStatement,
            $this->parser->getClassStatement(
                array(
                    'item' => 'new Object',
                    0 => array('one' => 'two'),
                    'three' => 1
                )
            )
        );
    }

    /**
     * Returns an instance of exporter
     * 
     * @return PHPUnit_Framework_MockObject_MockObject|EcomDev_LayoutCompiler_Contract_ExporterInterface
     */
    private function createExporter()
    {
        $exporter = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_ExporterInterface');
        return $exporter;
    }

    /**
     * Creates a class for an layout item interface
     * 
     * @return string
     */
    private function createItemClass()
    {
        $item = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Layout_ItemInterface');
        $itemClass = get_class($item);
        return $itemClass;
    }
}