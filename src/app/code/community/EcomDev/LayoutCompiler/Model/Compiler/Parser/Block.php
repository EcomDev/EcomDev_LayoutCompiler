<?php
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;

/**
 * Parser for <block /> nodes in the layout
 *
 */
class EcomDev_LayoutCompiler_Model_Compiler_Parser_Block
    extends EcomDev_LayoutCompiler_Compiler_AbstractParser
{
    /**
     * Configures a block parser to use a specified class name
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
     * @param CompilerInterface $compiler
     * @param null|string $parentIdentifier
     * @param string[] $parentIdentifiers
     * @return string|string[]
     */
    public function parse(SimpleXMLElement $element,
                          CompilerInterface $compiler,
                          $parentIdentifier = null,
                          $parentIdentifiers = array())
    {
        $attributes = array();

        foreach ($element->attributes() as $attribute => $value) {
            $attributes[$attribute] = (string)$value;
        }

        $arguments = array();

        $arguments[] = $attributes;

        if (isset($attributes['name'])) {
            $arguments[] = $attributes['name'];
        } else {
            $arguments[] = null;
        }

        $arguments[] = $parentIdentifier;
        $arguments[] = $parentIdentifiers;

        if ($parentIdentifier !== null && !in_array($parentIdentifier, $parentIdentifiers, true)) {
            $parentIdentifiers[] = $parentIdentifier;
        }

        if (!empty($attributes['parent'])) {
            $arguments[2] = $attributes['parent'];
            // Override parent identifiers, since parent identifier is added to it now
            $arguments[3] = $parentIdentifiers;
        }

        $statements = $compiler->parseElements($element, $arguments[1], $parentIdentifiers);
        array_unshift($statements, $this->getClassStatement($arguments));
        return $statements;
    }
}
