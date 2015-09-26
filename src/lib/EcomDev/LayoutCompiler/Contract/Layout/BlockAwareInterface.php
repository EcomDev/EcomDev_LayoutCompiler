<?php

/**
 * Interface to represent a block aware operations 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Layout_BlockAwareInterface
{
    /**
     * Identifier of the related block
     * 
     * @return string
     */
    public function getBlockId();

    /**
     * Returns list of possible block identifiers, that might be affected.
     * 
     * Includes block identifier itself.
     *
     * @return string[]
     */
    public function getPossibleBlockIdentifiers();
}

