<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Compiler_Metadata as Metadata;

/**
 * Metadata factory class
 * 
 * Used to create metadata objects based on 
 */
class EcomDev_LayoutCompiler_Compiler_MetadataFactory
    implements EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface
{
    use EcomDev_LayoutCompiler_PathAwareTrait;
    
    /**
     * Returns an instance of new metadata object from source object
     *
     * @param \EcomDev_LayoutCompiler_Contract_Layout_SourceInterface $source
     * @param array $handles
     * @return \EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface
     */
    public function createFromSource(SourceInterface $source, array $handles = array())
    {
        return new Metadata($handles, $source->getId(), $source->getChecksum(), $this->getSavePath());
    }
}