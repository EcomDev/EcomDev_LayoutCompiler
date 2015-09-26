<?php

/**
 * Layout source 
 * 
 * @return array
 */
interface EcomDev_LayoutCompiler_Contract_Layout_SourceInterface
{
    /**
     * Returns array of simple xml objects, where key is a handle name
     * 
     * @return SimpleXmlElement[]
     * @throws RuntimeException in case of load error (malformed xml, etc) 
     */
    public function load();

    /**
     * Unique identifier of the layout object
     * 
     * @return string
     */
    public function getId();
    
    /**
     * Returns a checksum for a layout source
     * 
     * @return string
     */
    public function getChecksum();

    /**
     * Returns a reference path to an object (e.g. a file path or a string identifier in database)
     * 
     * @return string
     */
    public function getOriginalPath();
}
