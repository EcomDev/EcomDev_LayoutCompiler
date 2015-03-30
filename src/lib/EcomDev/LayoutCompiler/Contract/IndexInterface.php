<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;

/**
 * Interface that represents a metadata index
 * 
 */
interface EcomDev_LayoutCompiler_Contract_IndexInterface
    extends EcomDev_LayoutCompiler_Contract_PathAwareInterface,
            EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
{
    /**
     * Loads an index by metadata parameters
     * 
     * If no index file is found it returns false
     * 
     * @param array $parameters
     * @return bool
     */
    public function load(array $parameters);

    /**
     * Validate an index by using source list and comparing it with existing metadata objects
     * 
     * @param SourceInterface[] $sources
     * @return bool
     */
    public function update(array $sources);
    
    /**
     * Adds a metadata object into index
     * 
     * @param MetadataInterface $metadata
     * @return $this
     */
    public function addMetadata(MetadataInterface $metadata);

    /**
     * Returns list of handle php includes that can be used to process layout handle
     * 
     * @param $handle
     * @return string[]
     */
    public function getHandleIncludes($handle);
    
    /**
     * Saves an index metadata by parameters 
     * 
     * @param array $parameters
     * @return $this
     */
    public function save(array $parameters);
}
