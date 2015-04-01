<?php

use EcomDev_LayoutCompiler_Compiler_Parser_Remove as RemoveParser;

class EcomDev_LayoutCompiler_Compiler_Parser_RemoveTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;
    
    /**
     * Parser that is under test
     *
     * @var RemoveParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new RemoveParser('EcomDev_LayoutCompiler_Layout_Item_Remove');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
    }


    public function testItUsesClassNameFromConstructor()
    {
        $this->assertSame('EcomDev_LayoutCompiler_Layout_Item_Remove', $this->parser->getClassName()); 
    }
    
    public function testItParsesAHandleNode()
    {
        $this->assertSame(
            "new EcomDev_LayoutCompiler_Layout_Item_Remove('test_block')",
            $this->parser->parse(new SimpleXMLElement('<remove name="test_block"/>'), $this->createCompiler())
        );
    }
    
    public function testItReturnsFalseIfDataIsInvalid()
    {
        $this->assertFalse(
            $this->parser->parse(new SimpleXMLElement('<remove some_attribute="test_handle"/>'), $this->createCompiler())
        );
    }
}