<?php

/**
 * Interface to represent a block aware operations 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_BlockAwareInterface
{
    /**
     * Can be a string or an array of strings
     * 
     * @return string|string[]
     */
    public function getBlockName();
}
