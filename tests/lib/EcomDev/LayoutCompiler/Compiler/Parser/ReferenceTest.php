<?php

use EcomDev_LayoutCompiler_Compiler_Parser_Reference as ReferenceParser;

class EcomDev_LayoutCompiler_Compiler_Parser_ReferenceTest
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    /**
     * Parser that is under test
     *
     * @var ReferenceParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new ReferenceParser();
    }
    
    public function testItParsesAReferenceNodeAndCallsParseElementsMethodOfCompiler()
    {
        $element = new SimpleXMLElement('<reference name="block" />');
        $compiler = $this->createCompiler();
        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, 'block')
            ->willReturn(array('Item1()'));
        
        $this->assertSame(
            array('Item1()'), 
            $this->parser->parse($element, $compiler, 'some_data', array('some_data'))
        );
    }
    
    public function testItParsesAReferenceNodeAndDoesParseElementsEvenIfNameIsNotSpecified()
    {
        $element = new SimpleXMLElement('<reference another_attribute="block" />');
        $compiler = $this->createCompiler();
        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, null)
            ->willReturn(array());

        $this->assertSame(
            array(),
            $this->parser->parse($element, $compiler, 'some_data', array('some_data'))
        );
    }
}