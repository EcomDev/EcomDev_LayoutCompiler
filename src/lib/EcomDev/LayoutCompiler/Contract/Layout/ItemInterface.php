<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;

/**
 * Layout executable item interface
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Layout_ItemInterface
{
    /**
     * When instruction is added to processor
     * 
     * This type never executed via execute command, it is executed recursively
     * 
     * @var int
     */
    const TYPE_INITIALIZE = 0;

    /**
     * When instruction is executed after items has been added to processor
     * 
     * @var int
     */
    const TYPE_POST_INITIALIZE = 1;
    
    /**
     * Type when the instruction is rendered at layout load time (default)
     * 
     */
    const TYPE_LOAD = 2;

    /**
     * Executes an action on layout object
     * 
     * @param LayoutInterface $layout
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function execute(LayoutInterface $layout, ProcessorInterface $processor);

    /**
     * Type of operation, @see const TYPE_* for details
     *
     * 
     * @return int
     */
    public function getType();
}
