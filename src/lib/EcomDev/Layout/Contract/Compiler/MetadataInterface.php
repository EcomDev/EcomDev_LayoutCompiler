<?php

use EcomDev_Layout_Contract_Layout_SourceInterface as SourceInterface;

/**
 * Metadata interface for a compiled layout update
 */
interface EcomDev_Layout_Contract_Compiler_MetadataInterface
    extends EcomDev_Layout_Contract_PathAwareInterface
{
    /**
     * Returns a list of handles associated to a compiled file
     * 
     * @return array
     */
    public function getHandles();

    /**
     * Returns a file name that contains handle instruction file
     * 
     * @param string $handle
     * @return string
     */
    public function getHandlePath($handle);

    /**
     * Returns an identifier of source object
     * 
     * @return string
     */
    public function getId();

    /**
     * Returns checksum of source objects, 
     * from which this one has been created
     * 
     * @return string
     */
    public function getChecksum();

    /**
     * Create a metadata object from state
     * 
     * @param array $data
     * @return array
     */
    public function __set_state(array $data);

    /**
     * Should create a new instance of metadata based on source information
     * 
     * @param SourceInterface $source
     * @param string[] $handles handles used in source file
     * @return $this
     */
    public static function fromSource(SourceInterface $source, array $handles);
}
