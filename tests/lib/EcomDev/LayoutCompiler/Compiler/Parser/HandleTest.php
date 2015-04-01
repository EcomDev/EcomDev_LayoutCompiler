<?php

use EcomDev_LayoutCompiler_Compiler_Parser_Handle as HandleParser;

class EcomDev_LayoutCompiler_Compiler_Parser_HandleTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;
    
    /**
     * Parser that is under test
     *
     * @var HandleParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new HandleParser('EcomDev_LayoutCompiler_Layout_Item_Include');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
    }


    public function testItUsesClassNameFromConstructor()
    {
        $this->assertSame('EcomDev_LayoutCompiler_Layout_Item_Include', $this->parser->getClassName()); 
    }
    
    public function testItParsesAHandleNode()
    {
        $this->assertSame(
            "new EcomDev_LayoutCompiler_Layout_Item_Include('test_handle')",
            $this->parser->parse(new SimpleXMLElement('<update handle="test_handle"/>'), $this->createCompiler())
        );
    }
    
    public function testItReturnsFalseIfDataIsInvalid()
    {
        $this->assertFalse(
            $this->parser->parse(new SimpleXMLElement('<update name="test_handle"/>'), $this->createCompiler())
        );
    }
}