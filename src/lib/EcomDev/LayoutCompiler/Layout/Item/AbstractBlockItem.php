<?php


use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

/**
 * Abstract block aware item (regular layout operation)
 */
abstract class EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem
    implements EcomDev_LayoutCompiler_Contract_Layout_ItemInterface, 
               EcomDev_LayoutCompiler_Contract_Layout_BlockAwareInterface
{
    public function __construct()
    {
        
    }

    /**
     * Can be a string or an array of strings
     *
     * @return string|string[]
     */
    public function getBlockName()
    {
        // TODO: Implement getBlockName() method.
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
        // TODO: Implement execute() method.
    }

    /**
     * Type of operation, @see const TYPE_* for details
     *
     *
     * @return int
     */
    public function getType()
    {
        // TODO: Implement getType() method.
    }

}