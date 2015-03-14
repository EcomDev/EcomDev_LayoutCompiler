<?php

use EcomDev_LayoutCompiler_Contract_ExporterInterface as ExporterInterface;

interface EcomDev_LayoutCompiler_Contract_ExporterAwareInterface
{
    /**
     * Sets an exporter into object
     * 
     * @param ExporterInterface $exporter
     * @return string
     */
    public function setExporter(ExporterInterface $exporter);

    /**
     * Returns an exporter for an object
     * 
     * @return ExporterInterface
     */
    public function getExporter();
}