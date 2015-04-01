<?php

use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

/**
 * Layout loader interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface
{
    /**
     * Loads handle items
     * 
     * @param string|string[] $handleName
     * @return ItemInterface[]
     */
    public function load($handleName);

    /**
     * Check if handle has been already loaded
     * 
     * @param string $handle
     * @return bool
     */
    public function isLoaded($handle);

    /**
     * Loads a handles into layout processor
     * 
     * @param string|string[] $handleName
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function loadIntoProcessor($handleName, ProcessorInterface $processor);

    /**
     * Resets state of loader
     * 
     * @return $this
     */
    public function reset();
}
