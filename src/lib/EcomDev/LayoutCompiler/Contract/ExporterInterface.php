<?php

use EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface as ExpressionInterface;

/**
 * Exporter of php variables into php executable code
 *
 */
interface EcomDev_LayoutCompiler_Contract_ExporterInterface
{
    /**
     * Exports value into php executable code
     * 
     * @param array|string|int|stdClass|ExpressionInterface $value
     * @return string
     */
    public function export($value);
}