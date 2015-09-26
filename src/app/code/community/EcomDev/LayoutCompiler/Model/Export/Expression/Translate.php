<?php

use EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface as ExpressionInterface;

/**
 * Translation expression for exporter
 *
 */
class EcomDev_LayoutCompiler_Model_Export_Expression_Translate
    implements EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface
{
    /**
     * Helper string
     *
     * @var string
     */
    private $helper;

    /**
     * Translate text
     *
     * @var string
     */
    private $string;

    /**
     * Configures a helper for a translation
     *
     * @param string $helper
     * @param string $string
     */
    public function __construct($helper, $string)
    {
        $this->helper = $helper;
        $this->string = $string;
    }

    /**
     * Expression rendering
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->string instanceof ExpressionInterface) {
            $value = (string)$this->string;
        } else {
            $value = var_export($this->string, true);
        }

        return sprintf(
            'Mage::helper(%s)->__(%s)',
            var_export($this->helper, true),
            $value
        );
    }
}
