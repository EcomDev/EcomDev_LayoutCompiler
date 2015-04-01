<?php
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;

class EcomDev_LayoutCompiler_Compiler_Parser_Reference
    implements EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface
{
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
                          CompilerInterface $compiler,
                          $blockIdentifier = null,
                          $parentIdentifiers = array())
    {
        $blockIdentifier = (isset($element->attributes()->name) ? $element->attributes()->name : null);
        return $compiler->parseElements($element, $blockIdentifier);
    }
}