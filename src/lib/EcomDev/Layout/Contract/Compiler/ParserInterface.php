<?php

/**
 * Compiler parser interface 
 * 
 */
interface EcomDev_Layout_Contract_Compiler_ParserInterface
{
    /**
     * Parses a simple xml element and returns an executable string for a layout node
     * 
     * @param SimpleXMLElement $element
     * @return string|string[]
     */
    public function parse(SimpleXMLElement $element);
}
