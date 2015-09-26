<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;

/**
 * Metadata factory interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface
    extends EcomDev_LayoutCompiler_Contract_PathAwareInterface
{
    /**
     * Returns an instance of new metadata object from source object
     * 
     * @param SourceInterface $source
     * @param array $handles
     * @return MetadataInterface
     */
    public function createFromSource(SourceInterface $source, array $handles = array());
}
