<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;

/**
 * Metadata instance
 * 
 * 
 */
class EcomDev_LayoutCompiler_Compiler_Metadata
    implements EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface
{
    use EcomDev_LayoutCompiler_PathAwareTrait;
    
    /**
     * Identifier
     * 
     * @var string
     */
    private $id;

    /**
     * Checksum 
     * 
     * @var string
     */
    private $checksum;

    /**
     * Handles that are available in compiled file
     * 
     * @var string[]
     */
    private $handles;

    /**
     * Creates a new instance of metadata
     * 
     * @param array $handles
     * @param string $id
     * @param string $checksum
     * @param string $savePath
     */
    public function __construct(array $handles, $id, $checksum, $savePath)
    {
        $this->handles = $handles;
        $this->id = $id;
        $this->checksum = $checksum;
        $this->setSavePath($savePath);
    }

    /**
     * Returns a list of handles associated to a compiled file
     *
     * @return string[]
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     * Returns a file name that contains handle instruction file
     *
     * @param string $handle
     * @return string
     */
    public function getHandlePath($handle)
    {
        return sprintf('%s/%s_%s.php', $this->getSavePath(), $this->getId(), $handle);
    }

    /**
     * Returns an identifier of source object
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns checksum of source objects,
     * from which this one has been created
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Validates metadata object
     *
     * @param SourceInterface $source
     * @return bool
     */
    public function validate(SourceInterface $source)
    {
        return $this->getChecksum() === $source->getChecksum();
    }

    /**
     * Create a metadata object from state
     *
     * @param array $data
     * @return $this
     */
    public static function __set_state(array $data)
    {
        return new self($data['handles'], $data['id'], $data['checksum'], $data['savePath']);
    }
}
