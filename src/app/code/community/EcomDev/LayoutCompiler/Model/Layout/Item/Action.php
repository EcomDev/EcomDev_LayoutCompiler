<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

class EcomDev_LayoutCompiler_Model_Layout_Item_Action
    extends EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
{
    /**
     * @var string[]
     */
    private $options;

    /**
     * A closure, that is going to be used for calling a block action
     *
     * @var \Closure
     */
    private $callback;

    /**
     * Constructs an action item based on passed options
     *
     * @param string[] $options
     * @param string $blockId
     * @param Closure $callback
     * @param array $parentBlockIds
     */
    public function __construct($options, $blockId, \Closure $callback, array $parentBlockIds = array())
    {
        parent::__construct($blockId, $parentBlockIds);
        $this->options = $options;
        $this->callback = $callback;
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
        if (!empty($this->options['ifconfig']) && !Mage::getStoreConfigFlag($this->options['ifconfig'])) {
            return $this;
        }

        $callback = $this->callback;
        $block = $layout->findBlockById($this->getBlockId());
        if ($block !== false) {
            $callback($block);
        }

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
        return 'ACTION: ' . $this->getBlockId() . '->' . $this->options['method'];
    }
}
