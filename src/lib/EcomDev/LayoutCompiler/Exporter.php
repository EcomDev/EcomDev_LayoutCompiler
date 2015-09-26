<?php

use EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface as ExpressionInterface;

/**
 * Exporter of the data into php code
 * 
 */
class EcomDev_LayoutCompiler_Exporter
    implements EcomDev_LayoutCompiler_Contract_ExporterInterface
{
    /**
     * Exports value into php executable code
     *
     * @param array|string|null|int|stdClass|ExpressionInterface $value
     * @return string
     */
    public function export($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value instanceof ExpressionInterface) {
            return (string)$value;
        }
        
        if ($value instanceof stdClass) {
            return sprintf('(object)%s', $this->export((array)$value));
        }
        
        if (is_object($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'An object of class "%s" does not implement %s',
                    get_class($value),
                    'EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface'
                )
            );
        }
        
        if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $result[] = sprintf(
                    "%s => %s",
                    var_export($k, true),
                    $this->export($v)
                );
            }

            return sprintf("array(%s)", implode(", ", $result));
        }

        return var_export($value, true);
    }
}
