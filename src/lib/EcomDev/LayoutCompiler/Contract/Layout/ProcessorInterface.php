<?php

use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;

/**
 * Layout interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface
    extends EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
{
    /**
     * Adds an item to layout
     * 
     * @param ItemInterface $item
     * @param bool $discoverRelations
     * @return $this
     */
    public function addItem(ItemInterface $item, $discoverRelations = true);

    /**
     * Adds an item of block to another block
     *
     * @param EcomDev_LayoutCompiler_Contract_Layout_ItemInterface $item
     * @param string $blockIdentifier
     * @return $this
     */
    public function addItemRelation(ItemInterface $item, $blockIdentifier);

    /**
     * Removes an item from a layout
     * 
     * @param ItemInterface $item
     * @return $this
     */
    public function removeItem(ItemInterface $item);

    /**
     * Returns item list by block identifier
     * 
     * @param string $identifier
     * @return ItemInterface[]
     */
    public function findItemsByBlockId($identifier);

    /**
     * Returns item list by type of operation
     *
     * @param int $type
     * @return ItemInterface[]
     */
    public function findItemsByType($type);
    
    /**
     * Returns item list by block identifier and type of load
     *
     * @param string $identifier
     * @param int $type
     * @return ItemInterface[]
     */
    public function findItemsByBlockIdAndType($identifier, $type);
        
    /**
     * Execute an type of action depending on a layout instance
     * 
     * @param int $type
     * @return string
     */
    public function execute($type);

    /**
     * Resets items to be executed
     * 
     * @return $this
     */
    public function reset();
}
