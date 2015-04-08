<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;

/**
 * Layout Aware interface default implementation
 * 
 */
trait EcomDev_LayoutCompiler_LayoutAwareTrait
{
    /**
     * Layout interface
     * 
     * @var LayoutInterface
     */
    private $layout;
    
    /**
     * Set an instance of layout as a binding
     *
     * @param LayoutInterface $layout
     * @return $this
     */
    public function setLayout(LayoutInterface $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Returns an instance of assigned layout
     *
     * @return LayoutInterface
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
