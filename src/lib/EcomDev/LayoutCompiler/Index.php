<?php

use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;

class EcomDev_LayoutCompiler_Index 
    implements EcomDev_LayoutCompiler_Contract_IndexInterface, 
               EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface
{
    use EcomDev_LayoutCompiler_PathAwareTrait;
    use EcomDev_LayoutCompiler_LayoutAwareTrait;
    use EcomDev_LayoutCompiler_ErrorProcessorAwareTrait;

    /**
     * Metadata list
     * 
     * @var MetadataInterface[]
     */
    protected $metadata = array();

    /**
     * Metadata list by handle name
     * 
     * @var MetadataInterface[][]
     */
    protected $metadataByHandle = array();
    
    /**
     * Loads an index by metadata parameters
     *
     * If no index file is found it returns false
     *
     * @param array $parameters
     * @return bool
     */
    public function load(array $parameters)
    {
        
    }

    /**
     * Update an index by using source list and comparing it with existing metadata objects
     *
     * @param \EcomDev_LayoutCompiler_Contract_Layout_SourceInterface[] $sources
     * @return bool
     */
    public function update(array $sources)
    {
        $originalMetadata = $this->metadata;
        $this->metadata = array();
        $this->metadataByHandle = array();
        $compiler = $this->getLayout()
            ->getCompiler();
        
        foreach ($sources as $source) {
            $oldMetadata = null;
            $sourceId = $source->getId();
            
            if (isset($originalMetadata[$sourceId])) {
                $oldMetadata = $originalMetadata[$sourceId];
            }
            try {
                $newMetadata = $compiler->compile($source, $oldMetadata);
                $this->addMetadata($newMetadata);
            } catch (Exception $e) {
                $this->reportException($e);
            }
            
        }
        
        return $this;
    }

    /**
     * Adds a metadata object into index
     *
     * @param MetadataInterface $metadata
     * @return $this
     */
    public function addMetadata(MetadataInterface $metadata)
    {
        $id = $metadata->getId();
        
        $metadataItem = array(
            $id => $metadata
        );
        
        $this->metadata += $metadataItem;
        foreach ($metadata->getHandles() as $handle) {
            if (!isset($this->metadataByHandle[$handle])) {
                $this->metadataByHandle[$handle] = array();
            }
            
            $this->metadataByHandle[$handle] += $metadataItem;
        }
        
        
        return $this;
    }
    
    /**
     * Returns list of handle php includes that can be used to process layout handle
     *
     * @param $handle
     * @return string[]
     */
    public function getHandleIncludes($handle)
    {
        if (!isset($this->metadataByHandle[$handle])) {
            return array();
        }
        
        $result = array();
        
        foreach ($this->metadataByHandle[$handle] as $metadata) {
            $result[] = $metadata->getHandlePath($handle);
        }
        
        return $result;
    }

    /**
     * Saves an index metadata by parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function save(array $parameters)
    {
        // TODO: Implement save() method.
    }
}