<?php

use EcomDev_Layout_Contract_Layout_UpdateInterface as UpdateInterface;
use EcomDev_Layout_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_Layout_Contract_Layout_LoaderInterface as LoaderInterface;
use EcomDev_Layout_Contract_CompilerInterface as CompilerInterface;

/**
 * Layout interface
 * 
 */
interface EcomDev_Layout_Contract_LayoutInterface
    extends EcomDev_Layout_Contract_Cache_AwareInterface, 
            EcomDev_Layout_Contract_PathAwareInterface
{
    /**
     * Returns an instance of layout wrapper
     * 
     * @return ProcessorInterface
     */
    public function getProcessor();

    /**
     * Returns an instance of update interface
     * 
     * @return UpdateInterface
     */
    public function getUpdate();

    /**
     * Returns an instance of loader object
     *
     * @return LoaderInterface
     */
    public function getLoader();
    
    /**
     * Returns an instance of loader object
     *
     * @return CompilerInterface
     */
    public function getCompiler();

    /**
     * Loads instructions 
     * 
     * @return $this
     */
    public function load();

    /**
     * Loads instructions
     *
     * @return $this
     */
    public function generateXml();

    /**
     * Generate layout blocks
     * 
     * @return $this
     */
    public function generateBlocks();

    /**
     * Finds a block by its identifier
     *
     * @param string $identifier
     * @return object
     */
    public function findBlockById($identifier);

    /**
     * Returns an instance of a block
     *
     * @param string $alias
     * @param string $identifier
     * @return object
     */
    public function newBlock($alias, $identifier = null);
}
