<?php

interface EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface
{
    /**
     * Reports exception occurred somewhere in the code
     * 
     * @param Exception $e
     * @return $this
     */
    public function processException(Exception $e);
}
    
