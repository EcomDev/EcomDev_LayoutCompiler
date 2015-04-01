<?php

class EcomDev_LayoutCompiler_Compiler_Parser_Remove
    extends EcomDev_LayoutCompiler_Compiler_AbstractParser
{
    /**
     * Sets class name for parser
     * 
     * @param string $className
     */
    public function __construct($className)
    {
        $this->setClassName($className);
    }
    
    /**
     * Parses a simple xml element and returns an executable string for a layout node
     *
     * @param SimpleXMLElement $element
     * @param \EcomDev_LayoutCompiler_Contract_CompilerInterface $compiler
     * @param null|string $blockIdentifier
     * @param string[] $parentIdentifiers
     * @return string|string[]
     */
    public function parse(SimpleXMLElement $element,
                          \EcomDev_LayoutCompiler_Contract_CompilerInterface $compiler,
                          $blockIdentifier = null,
                          $parentIdentifiers = array())
    {
        if (empty($element->attributes()->name)) {
            return false;
        }
        
        return $this->getClassStatement(array((string)$element->attributes()->name));
    }
}