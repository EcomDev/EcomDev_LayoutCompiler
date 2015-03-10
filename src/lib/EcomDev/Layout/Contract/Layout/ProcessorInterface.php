<?php

use EcomDev_Layout_Contract_Layout_ItemInterface as ItemInterface;

/**
 * Layout interface
 * 
 */
interface EcomDev_Layout_Contract_Layout_ProcessorInterface
    extends EcomDev_Layout_Contract_LayoutAwareInterface
{
    /**
     * Adds an item to layout
     * 
     * @param ItemInterface $item
     * @return $this
     */
    public function addItem(ItemInterface $item);

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
     * Returns item list by block identifier
     *
     * @param int $type
     * @return ItemInterface[]
     */
    public function findItemsByType($type);
    
    /**
     * Returns item list by block identifier
     *
     * @param string $identifier
     * @param string $classOrInterface
     * @return ItemInterface[]
     */
    public function findItemsByBlockIdAndType($identifier, $classOrInterface);
        
    /**
     * Execute an type of action depending on a layout instance
     * 
     * @param int $type
     * @return string
     */
    public function execute($type);
}
