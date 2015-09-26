<?php
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;

class EcomDev_LayoutCompiler_Compiler_Parser_Reference
    implements EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface
{
    /**
     * Id attribute for a parser
     *
     * @var string
     */
    private $idAttribute;

    /**
     * Creates a new parser with an attribute identifier
     *
     * @param string $idAttribute
     */
    public function __construct($idAttribute = 'name')
    {
        $this->idAttribute = $idAttribute;
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
                          CompilerInterface $compiler,
                          $blockIdentifier = null,
                          $parentIdentifiers = array())
    {
        if ($blockIdentifier !== null && !in_array($blockIdentifier, $parentIdentifiers, true)) {
            $parentIdentifiers[] = $blockIdentifier;
        }

        $blockIdentifier = (
            isset($element->attributes()->{$this->idAttribute}) ?
                (string)$element->attributes()->{$this->idAttribute}
                : null
        );

        return $compiler->parseElements($element, $blockIdentifier, $parentIdentifiers);
    }
}
