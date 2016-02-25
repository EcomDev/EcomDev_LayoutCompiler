<?php

use EcomDev_LayoutCompiler_Exporter as Exporter;

/**
 * 
 * 
 */
class EcomDev_LayoutCompiler_ExporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Exporter
     */
    private $exporter; 
    
    protected function setUp()
    {
        $this->exporter = new Exporter();
    }

    /**
     * @param $expectedString
     * @param $argument
     * @dataProvider dataProviderExportArgumentScalar
     */
    public function testItExportsScalarArgument($expectedString, $argument)
    {
        $this->assertSame(
            $expectedString, $this->exporter->export($argument)
        );
    }

    public function dataProviderExportArgumentScalar()
    {
        return array(
            array("'string'", 'string'),
            array("'string\\''", 'string\''),
            array("'\"string\"'", '"string"'),
            array("'string' . \"\\0\" . 'another'", "string\0another"),
            array((PHP_VERSION_ID > 70000 ? "0.0" : "0"), 0.00), // Issue with PHP 7.0 var export implementation :)
            array("1", 1),
            array('null', null),
            array('false', false),
            array('true', true),
            array("100", 100)
        );
    }

    /**
     * @param string $expectedString
     * @param array|object $argument
     * @dataProvider dataProviderExportComplexData
     */
    public function testItExportsSimpleComplexDataArgument($expectedString, $argument)
    {
        $this->assertSame(
            $expectedString, $this->exporter->export($argument)
        );
    }

    public function dataProviderExportComplexData()
    {
        $objectSample = new stdClass();
        $objectSample->key = 'key1';
        $objectSample->value = new stdClass();
        $objectSample->value->key = 'key2';
        $objectSample->value->value = array('item');
        
        $expectedObjectString = sprintf(
            "(object)array('key' => 'key1', 'value' => %s)", 
            sprintf(
                "(object)array('key' => 'key2', 'value' => %s)",
                "array(0 => 'item')"
            )
        );
        
        return array(
            array("array(0 => '1', 1 => '2')", array('1', '2')),
            array("array('one' => 'two')", array('one' => 'two')),
            array($expectedObjectString, $objectSample),
        );
    }

    public function testItExportsSingleExpressionArgument()
    {
        $expression = $this->createExpression('my_expression');

        $this->assertSame(
            'my_expression',
            $this->exporter->export($expression)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage An object of class "EcomDev_LayoutCompiler_ExporterTest" does not implement EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface  
     */
    public function testItShouldRiseAnExceptionIfObjectIsNotExportable()
    {
       $this->exporter->export($this);
    }

    public function testItExportsExpressionObjectsInArray()
    {
        $expressionOne = $this->createExpression('my_expression_1', 2);
        $expressionTwo = $this->createExpression('my_expression_2');
        $expressionThree = $this->createExpression('my_expression_3');
        $argument = array(
            'expression1.1' => $expressionOne,
            'level1' => array(
                'level2' => array(
                    'expression1.2' => $expressionOne
                ),
                'expression2' => $expressionTwo
            ),
            'level1.1' => array(
                'level2.1' => array(
                    'expression3' => $expressionThree
                )
            )
        );

        $expectedString = "array('expression1.1' => my_expression_1, 'level1' => array('level2' => "
            . "array('expression1.2' => my_expression_1), 'expression2' => my_expression_2), "
            . "'level1.1' => array('level2.1' => array('expression3' => my_expression_3)))";

        $this->assertSame(
            $expectedString,
            $this->exporter->export($argument)
        );
    }

    private function createExpression($value, $times = 1)
    {
        $expression = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface');
        $expression->expects($this->exactly($times))
            ->method('__toString')
            ->willReturn($value);

        return $expression;
    }
}
