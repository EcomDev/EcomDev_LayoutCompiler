<?php

use EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface as ErrorProcessorInterface;

trait EcomDev_LayoutCompiler_ErrorProcessorAwareTrait
{
    /**
     * Error processors stack
     * 
     * @var ErrorProcessorInterface[]
     */
    private $errorProcessors = array();

    /**
     * Adds error processor
     *
     * @param ErrorProcessorInterface $errorProcessor
     * @return $this
     */
    public function addErrorProcessor(ErrorProcessorInterface $errorProcessor)
    {
        if (in_array($errorProcessor, $this->errorProcessors, true)) {
            return $this;
        }
        
        $this->errorProcessors[] = $errorProcessor;
        return $this;
    }
    
    /**
     * Reports an exception to all error processors that have been added
     *
     * @param Exception $exception
     * @return $this
     */
    public function reportException(Exception $exception)
    {
        foreach ($this->errorProcessors as $processor) {
            try {
                $processor->processException($exception);
            } catch (Exception $e) {
                continue; // No way to report it anywhere
            }
        }
        return $this;
    }
}
