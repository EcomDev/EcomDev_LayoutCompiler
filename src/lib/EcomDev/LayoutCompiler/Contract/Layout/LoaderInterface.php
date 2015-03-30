<?php

use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;

/**
 * Layout loader interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface
    extends EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
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
     * @return $this
     */
    public function loadIntoProcessor($handleName);
}
