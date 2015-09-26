<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_IndexInterface as IndexInterface;

interface EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface
    extends EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
{
    /**
     * Normal index type
     */
    const INDEX_NORMAL = 'normal';
    
    /**
     * Runtime index type
     * 
     * @var string
     */
    const INDEX_RUNTIME = 'runtime';
    
    /**
     * Adds a handle to the list of available
     * 
     * @param string|string[] $handle
     * @return $this
     */
    public function addHandle($handle);

    /**
     * Returns list of added handles
     * 
     * @return string[]
     */
    public function getHandles();

    /**
     * Resets handle list
     * 
     * @return $this
     */
    public function resetHandles();

    /**
     * Loads layout handles or a handle
     *
     * @param string|string[] $handles
     * @return $this
     */
    public function load($handles = array());

    /**
     * Loads runtime updates into the processor
     *
     * @return $this
     */
    public function loadRuntime();

    /**
     * Returns a list of sources by type
     * 
     * @param string $type
     * @return SourceInterface[]
     */
    public function getSources($type = self::INDEX_NORMAL);

    /**
     * Loads index for a normal type of data
     *
     * @param string $type type of the index
     * @return bool
     */
    public function loadIndex($type = self::INDEX_NORMAL);

    /**
     * Returns an instance of index with a specified type
     *
     * @param string $type type of the index
     * @return IndexInterface
     */
    public function getIndex($type = self::INDEX_NORMAL);
}
