<?php

class EcomDev_LayoutCompiler_Exporter_Expression
    implements EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface
{
    /**
     * @var string
     */
    private $expression;
    
    /**
     * Constructs an expression object with value
     * 
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Renders expression value
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}
