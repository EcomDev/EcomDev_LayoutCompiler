<?php

use EcomDev_LayoutCompiler_Exporter_Expression as Expression; 
    
class EcomDev_LayoutCompiler_Exporter_ExpressionTest
    extends PHPUnit_Framework_TestCase
{
    public function testItRendersValueThatHasBeenPassedInConstructor()
    {
        $expression = new Expression('String value');
        $this->assertSame('String value', (string)$expression);
    }
}
