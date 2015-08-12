<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

class EcomDev_LayoutCompiler_Layout_Item_Block
    extends EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
{
    /**
     * List of options that have been used during creation of the object
     *
     * @var string[]
     */
    private $options;

    /**
     * Identifier of the parent block
     *
     * @var string
     */
    private $parentBlockId;

    /**
     * @param array $options
     * @param string $blockId
     * @param string $parentBlockId
     * @param string[] $parentBlockIds
     */
    public function __construct(
        array $options, $blockId, $parentBlockId,
        array $parentBlockIds = array())
    {
        parent::__construct($blockId, $parentBlockIds);
        $this->options = $options;
        $this->parentBlockId = $parentBlockId;
    }


    /**
     * Creates a block in the layout, based on item arguments
     *
     * @param LayoutInterface $layout
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function execute(LayoutInterface $layout, ProcessorInterface $processor)
    {
        $options = $this->options;

        if (!empty($options['class'])) {
            $classAlias = $options['class'];
        } elseif (!empty($options['type'])) {
            $classAlias = $options['type'];
        } else {
            return $this;
        }

        $options['parent'] = $this->parentBlockId;
        $layout->newBlock($classAlias, $this->getBlockId(), $options);
        return $this;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return 'BLOCK: ' . $this->getBlockId();
    }
}
