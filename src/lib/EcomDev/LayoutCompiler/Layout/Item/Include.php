<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface as UpdateInterface;

class EcomDev_LayoutCompiler_Layout_Item_Include 
    implements EcomDev_LayoutCompiler_Contract_Layout_ItemInterface
{
    /**
     * A handle that should be included
     *
     * @var string
     */
    private $handle;

    /**
     * Constructor of the include item for layout
     * 
     * @param string $handle
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
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
        $loader = $layout->getLoader();
        
        if (!$loader->isLoaded($this->handle)) {
            $loader->loadIntoProcessor($this->handle, $processor, $layout->getUpdate()->getIndex(
                UpdateInterface::INDEX_NORMAL
            ));
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
        return self::TYPE_INITIALIZE;
    }
    
}
