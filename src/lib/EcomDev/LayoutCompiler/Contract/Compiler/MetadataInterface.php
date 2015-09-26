<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;

/**
 * Metadata interface for a compiled layout update
 */
interface EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface
    extends EcomDev_LayoutCompiler_Contract_PathAwareInterface
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
     * Validates metadata object
     * 
     * @param $source
     * @return bool
     */
    public function validate(SourceInterface $source);
    
    /**
     * Create a metadata object from state
     * 
     * @param array $data
     * @return $this
     */
    public static function __set_state(array $data);
}
