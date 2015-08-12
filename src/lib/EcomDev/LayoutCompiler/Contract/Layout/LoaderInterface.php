<?php

use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_LayoutCompiler_Contract_IndexInterface as IndexInterface;

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
     * @param IndexInterface $index
     * @return ItemInterface[]
     */
    public function load($handleName, IndexInterface $index);

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
     * @param IndexInterface $index
     * @return $this
     */
    public function loadIntoProcessor($handleName, ProcessorInterface $processor, IndexInterface $index);

    /**
     * Resets state of loader
     * 
     * @return $this
     */
    public function reset();

    /**
     * And item into processer or just load list
     *
     * @param ItemInterface $item
     * @return $this
     */
    public function addItem(ItemInterface $item);

    /**
     * And item relation
     *
     * @param ItemInterface $item
     * @param string $relatedBlockIdentifier
     * @return $this
     */
    public function addItemRelation(ItemInterface $item, $relatedBlockIdentifier);
}
