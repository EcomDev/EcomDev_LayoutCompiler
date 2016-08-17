<?php

use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Compiler Index implementation
 * 
 * Makes it possible to 
 */
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
    private $metadata = array();

    /**
     * Metadata list by handle name
     * 
     * @var MetadataInterface[][]
     */
    private $metadataByHandle = array();

    /**
     * Array of ascii chars conversion to hex
     * 
     * @var string[]
     */
    private $hexTable;

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
        $file = $this->getIndexFileName($parameters);
        $result = @include $file;

        if ($result) {
            return true;
        }
        
        return false;
    }

    /**
     * Returns a file name for an index
     * 
     * @param array $parameters
     * @return string
     */
    private function getIndexFileName(array $parameters)
    {
        return $this->getSavePath() . '/' . $this->getIndexIdentifier($parameters) . '.php';
    }

    /**
     * Returns an identifier of index based on supplied parameters
     * 
     * @param array $parameters
     * @return string
     */
    public function getIndexIdentifier(array $parameters)
    {
        $identifier = 'index_'; 
        
        ksort($parameters);
        
        foreach ($parameters as $key => $value) {
            $identifier .= sprintf('%s_%s', $this->escapeHex($key), $this->escapeHex($value)) . '_';
        }
        
        return rtrim($identifier, '_');
    }

    /**
     * Escapes all non ascii letters and numbers to their hexadecimal presentation  
     * 
     * @param $string
     * @return string
     */
    private function escapeHex($string)
    {
        if ($this->hexTable === null) {
            $chars = array_merge(range(0, 47), range(58, 64), range(91, 94), array(96), range(123, 255));
            foreach ($chars as $char) {
                $this->hexTable[chr($char)] = 'x' . strtoupper(bin2hex(chr($char)));
            }
        }
        
        return strtr($string, $this->hexTable);
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
        $lines = array();
        foreach ($this->metadata as $metadata) {
            $lines[] = sprintf('$this->addMetadata(%s);', var_export($metadata, true));
        }
        
        if (!is_dir($this->getSavePath())) {
            mkdir($this->getSavePath(), 0755, true);
        }

        $tmpFile = $this->getSavePath() . DIRECTORY_SEPARATOR . uniqid('tempfile', TRUE);
        $filePath = $this->getIndexFileName($parameters);
        file_put_contents($tmpFile, "<?php \n" . implode("\n", $lines));
        rename($tmpFile, $filePath);
        chmod($filePath, 0644);
        return $this;
    }
}
