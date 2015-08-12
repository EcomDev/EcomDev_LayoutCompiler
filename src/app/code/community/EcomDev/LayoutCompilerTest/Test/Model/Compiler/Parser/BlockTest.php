<?php

use EcomDev_LayoutCompiler_Exporter_Expression as Expression;

/**
 * Test block parser for a compiler
 *
 *
 */

class EcomDev_LayoutCompilerTest_Test_Model_Compiler_Parser_BlockTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Compiler_Parser_Block
     */
    private $parser;

    protected function setUp()
    {
        $className = current(EcomDev_PHPUnit_Test_Case_Util::getAnnotationByNameFromClass(
            __CLASS__, 'className', 'method', $this->getName(false)
        ));

        $this->parser = new EcomDev_LayoutCompiler_Model_Compiler_Parser_Block($className);
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
    public function testItParsesABlockNodeAndCallsParseElementsMethodOfCompiler()
    {
        $element = new SimpleXMLElement('<block name="block_one" type="class/name" other_attribute="test/magento" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, 'block_one', array('block_zero', 'block_zero_and_half'))
            ->willReturn(array('Item1()'));

        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());

        $parserResult = $this->parser->parse($element, $compiler, 'block_zero_and_half', array('block_zero'));
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('name' => 'block_one', 'type' => 'class/name', 'other_attribute' => 'test/magento')",
                    "'block_one'",
                    "'block_zero_and_half'",
                    "array(0 => 'block_zero')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'block_one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero_and_half')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')"),
                'Item1()'
            ),
            $parserResult
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
     */
    public function testItParsesABlockNodeAndUsesParentAttributeInsteadOfParentIdentifierPassedAlong()
    {
        $element = new SimpleXMLElement('<block name="block_one" parent="block_two" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, 'block_one', array('block_zero', 'block_zero_and_half'))
            ->willReturn(array());

        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());

        $parserResult = $this->parser->parse($element, $compiler, 'block_zero', array('block_zero', 'block_zero_and_half'));
        $this->assertCount(5, $parserResult);
        $this->assertEquals(
            array(
                new Expression(sprintf(
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem(%s, %s, %s, %s), false)',
                    "array('name' => 'block_one', 'parent' => 'block_two')",
                    "'block_one'",
                    "'block_two'",
                    "array(0 => 'block_zero', 1 => 'block_zero_and_half')"
                )),
                new Expression("\$this->addItemRelation(\$item, 'block_one')"),
                new Expression("\$this->addItemRelation(\$item, 'block_two')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero')"),
                new Expression("\$this->addItemRelation(\$item, 'block_zero_and_half')")
            ),
            $parserResult
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_Block
     */
    public function testItParsesAlsoBlocksWithoutName()
    {
        $element = new SimpleXMLElement('<block name_none="block_one" parent="block_two" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, $this->stringStartsWith('ANONYMOUS_'), array('block_zero', 'block_zero_and_half'))
            ->willReturn(array());

        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());

        $this->assertStringMatchesFormat(
            sprintf(
                '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_Block(%s, %s, %s, %s), false)',
                "array('name_none' => 'block_one', 'parent' => 'block_two', '_ecomdev_system_option' => array('is_anonymous' => true))",
                "'ANONYMOUS_%s'",
                "'block_two'",
                "array(0 => 'block_zero', 1 => 'block_zero_and_half')"
            ),
            (string)current(
                $this->parser->parse($element, $compiler, 'block_zero', array('block_zero', 'block_zero_and_half'))
            )
        );
    }

    /**
     * @className EcomDev_LayoutCompiler_Layout_Item_Block
     */
    public function testItParsesAlsoBlocksWithAnonymousNameSuffix()
    {
        $element = new SimpleXMLElement('<block name=".blocksuffix" parent="block_two" />');
        $compiler = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CompilerInterface');

        $compiler->expects($this->once())
            ->method('parseElements')
            ->with($element, $this->stringStartsWith('ANONYMOUS_'), array('block_zero', 'block_zero_and_half'))
            ->willReturn(array());

        $this->parser->setExporter(new EcomDev_LayoutCompiler_Exporter());

        $this->assertStringMatchesFormat(
            sprintf(
                '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_Block(%s, %s, %s, %s), false)',
                "array('name' => '.blocksuffix', 'parent' => 'block_two', '_ecomdev_system_option' => array('is_anonymous' => true, 'anon_suffix' => 'blocksuffix'))",
                "'ANONYMOUS_%s'",
                "'block_two'",
                "array(0 => 'block_zero', 1 => 'block_zero_and_half')"
            ),
            (string)current(
                $this->parser->parse($element, $compiler, 'block_zero', array('block_zero', 'block_zero_and_half'))
            )
        );
    }
}
