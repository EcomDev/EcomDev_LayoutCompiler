<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_IndexInterface as IndexInterface;

interface EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface
    extends EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
{
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
     * Returns list of main sources that are stale for current theme, package, etc.
     * 
     * @return SourceInterface[]
     */
    public function getIndexSources();

    /**
     * Returns a list of runtime sources
     * 
     * @return SourceInterface[]
     */
    public function getRuntimeSources();

    /**
     * Loads an index or creates a new one based on internal data parameters
     *
     * @return $this
     */
    public function loadIndex();

    /**
     * Returns an instance of index
     * 
     * @return IndexInterface
     */
    public function getIndex();
}