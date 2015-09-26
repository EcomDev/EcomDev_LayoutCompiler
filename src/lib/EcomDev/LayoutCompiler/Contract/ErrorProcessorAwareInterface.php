<?php

use EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface as ErrorProcessorInterface;

/***
 * Error processor aware interface
 * 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface
{
    /**
     * Adds error processor
     * 
     * @param ErrorProcessorInterface $errorProcessor
     * @return $this
     */
    public function addErrorProcessor(ErrorProcessorInterface $errorProcessor);
}
