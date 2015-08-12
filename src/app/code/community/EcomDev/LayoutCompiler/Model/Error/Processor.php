<?php

/**
 * The only class that is not covered with unit tests in this module
 *
 * Used to report exceptions during loading of layout sources.
 */
class EcomDev_LayoutCompiler_Model_Error_Processor
    implements EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface
{
    /**
     * Reports exception occurred somewhere in the code
     *
     * @param Exception $e
     * @return $this
     */
    public function processException(Exception $e)
    {
        // Mage::logException($e); // removed as needs more testing for valid files
        return $this;
    }
}
