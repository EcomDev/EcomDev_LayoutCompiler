<?php

use EcomDev_Layout_Contract_LayoutInterface as LayoutInterface;
use EcomDev_Layout_Contract_Layout_ProcessorInterface as ProcessorInterface;

interface EcomDev_Layout_Contract_Layout_ItemInterface
{
    /**
     * When instruction is executed just after all the items has been added
     * 
     * @var int
     */
    const TYPE_INITIALIZE = 0;

    /**
     * When instruction is executed after item has been processed
     * 
     * @var int
     */
    const TYPE_POST_INITIALIZE = 1;
    
    /**
     * Type when the instruction is rendered at load time
     */
    const TYPE_LOAD = 2;

    /**
     * Executes an action on layout object
     * 
     * @param LayoutInterface $layout
     * @param ProcessorInterface $processor
     * @return string
     */
    public function execute(LayoutInterface $layout, ProcessorInterface $processor);

    /**
     * Type of operation, can be one of three
     * 
     * Compile time, pre-process
     * 
     * @return int
     */
    public function getType();
}
