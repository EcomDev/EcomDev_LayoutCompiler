<?php

use EcomDev_LayoutCompiler_Model_Export_Expression_Helper as HelperExpression;
use EcomDev_LayoutCompiler_Model_Export_Expression_Translate as TranslateExpression;
use EcomDev_LayoutCompiler_Exporter_Expression as Expression;

/**
 * Test case for action tag parser
 */
class EcomDev_LayoutCompilerTest_Test_Model_Compiler_Parser_ActionTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Compiler_Parser_Action
     */
    private $parser;

    protected function setUp()
    {
        $className = current(EcomDev_PHPUnit_Test_Case_Util::getAnnotationByNameFromClass(
            __CLASS__, 'className', 'method', $this->getName(false)
        ));

        $this->parser = new EcomDev_LayoutCompiler_Model_Compiler_Parser_Action($className);
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItSetsClassNameFromAConstructor()
    {
        $this->assertSame('EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem', $this->parser->getClassName());
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItDoesNotAddActionIfThereIsNoMethodSpecified()
    {
        $element = new SimpleXMLElement('<action item="block_one" ifconfig="some/path" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $this->assertFalse($this->parser->parse($element, $compiler, 'block_one', array('block_zero')));
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItReturnsFalseIfBlockIsNotSpecified()
    {
        $element = new SimpleXMLElement('<action method="setMethod" ifconfig="some/path" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $this->assertFalse($this->parser->parse($element, $compiler, null, array('block_zero')));
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItCorrectlyParsesAMethodCallWithoutArguments()
    {
        $element = new SimpleXMLElement('<action method="setMethod" block="one" ifconfig="some/path" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('method' => 'setMethod', 'block' => 'one', 'ifconfig' => 'some/path')",
                    "'one'",
                    'function ($block) { return $block->setMethod(); }',
                    "array(0 => 'block_zero')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')"),
            ),
            $this->parser->parse($element, $compiler, null, array('block_zero'))
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItCorrectlyParsesAMethodCallWithoutArgumentsAndOnlyParentBlockFromArguments()
    {
        $element = new SimpleXMLElement('<action method="setMethod" ifconfig="some/path" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('method' => 'setMethod', 'ifconfig' => 'some/path')",
                    "'one'",
                    'function ($block) { return $block->setMethod(); }',
                    "array(0 => 'block_zero')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')"),
            ),
            $this->parser->parse($element, $compiler, 'one', array('block_zero'))
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItCorrectlyParsesAMethodCallWithRegularArguments()
    {
        $element = new SimpleXMLElement(
            '<action method="setMethod" block="one" ifconfig="some/path">'
            . '<param><one>value1</one><two>value2</two></param>'
            . '<param2>value2</param2>'
            . '</action>'
        );
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('method' => 'setMethod', 'block' => 'one', 'ifconfig' => 'some/path')",
                    "'one'",
                    "function (\$block) { return \$block->setMethod(array('one' => 'value1', 'two' => 'value2'), 'value2'); }",
                    "array(0 => 'block_zero')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')")
            ),
            $this->parser->parse($element, $compiler, null, array('block_zero'))
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItCorrectlyParsesAMethodCallWithJsonArguments()
    {
        $element = new SimpleXMLElement(
            '<action method="setMethod" block="one" ifconfig="some/path" json="one two three four">'
            . '<one>{"key1":"value1"}</one><two>{"key2":"value2"}</two><three>true</three><four>1</four><five>Text</five>'
            . '</action>'
        );
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');
        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('method' => 'setMethod', 'block' => 'one', 'ifconfig' => 'some/path', 'json' => 'one two three four')",
                    "'one'",
                    "function (\$block) { return \$block->setMethod(array('key1' => 'value1'), array('key2' => 'value2'), true, 1, 'Text'); }",
                    "array(0 => 'block_zero')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')")
            ),
            $this->parser->parse($element, $compiler, null, array('block_zero'))
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     * @dataProvider dataProviderParseArgument
     */
    public function testItParsesArguments($argument, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->parser->parseArgument($argument));
    }

    public function dataProviderParseArgument()
    {
        return array(
            'empty_value' => array(
                new SimpleXMLElement('<param/>'),
                ''
            ),
            'int_value' => array(
                new SimpleXMLElement('<param>1</param>'),
                '1'
            ),
            'text_value' => array(
                new SimpleXMLElement('<param><![CDATA[textvalue]]></param>'),
                'textvalue'
            ),
            'array_value' => array(
                new SimpleXMLElement('<param><value><key>value</key></value><value2>one</value2></param>'),
                array('value' => array('key' => 'value'), 'value2' => 'one'),
            ),
            'helper_call_with_no_args' => array(
                new SimpleXMLElement('<param helper="helper/call/getName" />'),
                new HelperExpression('helper/call', 'getName')
            ),
            'helper_call_with_args' => array(
                new SimpleXMLElement('<param helper="helper/call/getName"><key>name</key><value>text</value></param>'),
                new HelperExpression('helper/call', 'getName', array('key' => 'name', 'value' => 'text'))
            )
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     * @dataProvider dataProviderTranslateArguments
     */
    public function testItReplacesTranslatableArguments($arguments, $node, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->parser->translateArguments($arguments, $node));
    }

    public function dataProviderTranslateArguments()
    {
        return array(
            'empty_list' => array(
                array(),
                new SimpleXMLElement('<node translate="value" module="test"/>'),
                array()
            ),
            'translate_with_specified_module' => array(
                array('label' => 'Text', 'other_stuff' => 'value'),
                new SimpleXMLElement('<node translate="label" module="test"/>'),
                array('label' => new TranslateExpression('test', 'Text'), 'other_stuff' => 'value'),
            ),
            'translate_with_default_module' => array(
                array('label' => 'Text', 'other_stuff' => 'value'),
                new SimpleXMLElement('<node translate="label" />'),
                array('label' => new TranslateExpression('core', 'Text'), 'other_stuff' => 'value'),
            ),
            'translate_multiple_items' => array(
                array('label' => 'Text', 'description' => 'Text 2', 'other_stuff' => 'value'),
                new SimpleXMLElement('<node translate="label description" />'),
                array('label' => new TranslateExpression('core', 'Text'),
                      'description' => new TranslateExpression('core', 'Text 2'),
                      'other_stuff' => 'value'),
            ),
            'translate_with_another_expression' => array(
                array('label' => new HelperExpression('test', 'setMethod'), 'other_stuff' => 'value'),
                new SimpleXMLElement('<node translate="label" />'),
                array(
                    'label' => new TranslateExpression('core', new HelperExpression('test', 'setMethod')),
                    'other_stuff' => 'value'
                )
            ),

            'translate_nested_item' => array(
                array('label' => array('sublabel' => array('final' => 'Text')), 'other_stuff' => 'value'),
                new SimpleXMLElement('<node translate="label.sublabel.final" />'),
                array(
                    'label' => array('sublabel' => array('final' => new TranslateExpression('core', 'Text'))),
                    'other_stuff' => 'value'
                )
            ),
        );
    }
}
