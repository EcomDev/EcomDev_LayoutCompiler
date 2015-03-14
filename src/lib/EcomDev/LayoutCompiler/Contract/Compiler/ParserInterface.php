<?php

use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;

/**
 * Compiler parser interface 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface
{
    /**
     * Parses a simple xml element and returns an executable string for a layout node
     * 
     * @param SimpleXMLElement $element
     * @param CompilerInterface $compiler
     * @param null|string $parentIdentifier
     * @return string|string[]
     */
    public function parse(SimpleXMLElement $element, 
                          CompilerInterface $compiler,
                          $parentIdentifier = null);
}
