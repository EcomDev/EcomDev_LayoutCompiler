<?php

/**
 * Abstract block aware item (regular layout operation)
 */
abstract class EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
    implements EcomDev_LayoutCompiler_Contract_Layout_ItemInterface, 
               EcomDev_LayoutCompiler_Contract_Layout_BlockAwareInterface
{
    /**
     * Block identifier
     * 
     * @var string
     */
    private $blockId;

    /**
     * Parent block identifiers
     * 
     * @var string[]
     */
    private $parentBlockIds;

    /**
     * @param string $blockId
     * @param string[] $parentBlockIds
     */
    public function __construct($blockId, array $parentBlockIds = array())
    {
        $this->blockId = $blockId;
        $this->parentBlockIds = $parentBlockIds;
    }

    /**
     * Identifier of the related block
     *
     * @return string
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Returns list of possible block identifiers, that might be affected.
     *
     * Includes block identifier itself.
     *
     * @return string[]
     */
    public function getPossibleBlockIdentifiers()
    {
        $parentBlockIds = $this->getParentBlockIds();
        array_unshift($parentBlockIds, $this->getBlockId());
        return $parentBlockIds;
    }

    /**
     * Returns list of parent block ids for this block
     * 
     * @return string[]
     */
    public function getParentBlockIds()
    {
        return $this->parentBlockIds;
    }
    
    /**
     * Type of operation, @see const TYPE_* for details
     *
     * @return int
     */
    public function getType()
    {
        return self::TYPE_LOAD;
    }
    
}
