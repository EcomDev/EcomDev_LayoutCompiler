<?php

use EcomDev_LayoutCompiler_Contract_LayoutInterface as LayoutInterface;

interface EcomDev_LayoutCompiler_Contract_LayoutAwareInterface
{
    /**
     * Set an instance of layout as a binding
     *
     * @param LayoutInterface $layout
     * @return $this
     */
    public function setLayout(LayoutInterface $layout);

    /**
     * Returns an instance of assigned layout
     *
     * @return LayoutInterface
     */
    public function getLayout();
}