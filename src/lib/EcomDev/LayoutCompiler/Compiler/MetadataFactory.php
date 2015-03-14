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

    /**
     * Save path
     * 
     * @var string
     */
    private $savePath;
    
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

    /**
     * Sets a save path for a compiled layout files
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;
        return $this;
    }

    /**
     * Returns a current save path for a compiler
     *
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }
}