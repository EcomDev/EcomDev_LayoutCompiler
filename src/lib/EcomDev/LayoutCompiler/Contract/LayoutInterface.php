<?php

use EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface as UpdateInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface as LoaderInterface;
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;
/**
 * Layout interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_LayoutInterface
    extends EcomDev_LayoutCompiler_Contract_PathAwareInterface,
            EcomDev_LayoutCompiler_Contract_FactoryAwareInterface
{
    /**
     * Returns an instance of layout wrapper
     * 
     * Should be created via factory
     * 
     * @return ProcessorInterface
     */
    public function getProcessor();

    /**
     * Returns an instance of update interface
     * 
     * Should be created via factory
     * 
     * @return UpdateInterface
     */
    public function getUpdate();

    /**
     * Returns an instance of loader object
     *
     * Should be created via factory
     *
     * @return LoaderInterface
     */
    public function getLoader();
    
    /**
     * Returns an instance of loader object 
     *
     * Should be created via factory
     *
     * @return CompilerInterface
     */
    public function getCompiler();
    
    /**
     * Loads instructions
     *
     * @return $this
     */
    public function generateXml();

    /**
     * Generate layout blocks
     *
     * @param SimpleXMLElement|null $parent
     * @return $this
     */
    public function generateBlocks($parent = null);

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
     * @param string $classAlias block class alias
     * @param string $identifier block identifier
     * @param string[] $options block options (before, after, template, etc)
     * @return object
     */
    public function newBlock(
        $classAlias, $identifier, array $options = array()
    );
}
