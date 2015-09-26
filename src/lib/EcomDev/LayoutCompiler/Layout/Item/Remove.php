<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

/**
 * Removes layout instructions that are related to particular block
 * 
 */
class EcomDev_LayoutCompiler_Layout_Item_Remove
    implements EcomDev_LayoutCompiler_Contract_Layout_ItemInterface
{
    /**
     * Identifier of the block which mentions should be removed
     * 
     * @var string
     */
    private $blockId;
    
    /**
     * Initializes block id which mentions should be removed from the layout structure
     * 
     * @param string $blockId
     */
    public function __construct($blockId)
    {
        $this->blockId = $blockId;
    }
    
    /**
     * Executes an action on layout object
     *
     * @param LayoutInterface $layout
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function execute(LayoutInterface $layout, ProcessorInterface $processor)
    {
        $items = $processor->findItemsByBlockIdAndType($this->blockId, self::TYPE_LOAD);
        
        foreach ($items as $item) {
            $processor->removeItem($item);
        }
        
        return $this;
    }

    /**
     * Type of operation, @see const TYPE_* for details
     *
     *
     * @return int
     */
    public function getType()
    {
        return self::TYPE_POST_INITIALIZE;
    }
}
