<?php

/**
 * Expression for a helper call
 *
 */
class EcomDev_LayoutCompiler_Model_Export_Expression_Helper
   implements EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface
{
    /**
     * Helper that should be
     *
     * @var string
     */
    private $helper;

    /**
     * Method name that should be called
     *
     * @var string
     */
    private $method;

    /**
     * Arguments to be used
     *
     * @var array
     */
    private $arguments;

    /**
     * Configures an expression
     *
     * @param string $helper
     * @param string $method
     * @param array $arguments
     */
    public function __construct($helper, $method, array $arguments = array())
    {
        $this->helper = $helper;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * Returns strings for a specified helper call arguments
     *
     * @return string
     */
    public function __toString()
    {
        $arguments = array();

        foreach ($this->arguments as $argument) {
            $arguments[] = var_export($argument, true);
        }

        return sprintf(
            'Mage::helper(%s)->%s(%s)',
            var_export($this->helper, true),
            $this->method,
            implode(', ', $arguments)
        );
    }
}
