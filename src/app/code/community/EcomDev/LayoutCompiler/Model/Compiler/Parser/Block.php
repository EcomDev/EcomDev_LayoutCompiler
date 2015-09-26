<?php
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;
use EcomDev_LayoutCompiler_Exporter_Expression as Expression;

/**
 * Parser for <block /> nodes in the layout
 *
 */
class EcomDev_LayoutCompiler_Model_Compiler_Parser_Block
    extends EcomDev_LayoutCompiler_Compiler_AbstractParser
{
    /**
     * Specifies which class to use for a parser output
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

        if (!empty($attributes['name']) && strpos($attributes['name'], '.') !== 0) {
            $arguments[] = $attributes['name'];
        } else {
            $arguments[] = uniqid('ANONYMOUS_', true);
            $arguments[0]['_ecomdev_system_option'] = array(
                'is_anonymous' => true
            );
            if (!empty($attributes['name'])) {
                $arguments[0]['_ecomdev_system_option']['anon_suffix'] = substr($attributes['name'], 1);
            }
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
        $blockStatements = array(
            new Expression(sprintf('$this->addItem($item = %s, false)', $this->getClassStatement($arguments)))
        );

        foreach (array_unique(
                     array_merge(array($arguments[1], $arguments[2]), $parentIdentifiers)
                 ) as $parentBlock) {
            $blockStatements[] = new Expression(
                sprintf('$this->addItemRelation($item, %s)', var_export($parentBlock, true))
            );
        }

        return array_merge($blockStatements, $statements);
    }
}
