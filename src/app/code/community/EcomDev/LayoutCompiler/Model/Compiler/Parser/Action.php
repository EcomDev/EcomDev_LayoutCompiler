<?php
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;
use EcomDev_LayoutCompiler_Exporter_Expression as Expression;

/**
 * Parser for an <action /> nodes in layout
 */
class EcomDev_LayoutCompiler_Model_Compiler_Parser_Action
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
     * @param null|string $blockIdentifier
     * @param string[] $parentIdentifiers
     * @return string|string[]
     */
    public function parse(SimpleXMLElement $element,
                          CompilerInterface $compiler,
                          $blockIdentifier = null,
                          $parentIdentifiers = array())
    {
        $attributes = array();
        foreach ($element->attributes() as $key => $value) {
            $attributes[$key] = (string)$value;
        }

        if (empty($attributes['method']) || (empty($blockIdentifier) && empty($attributes['block']))) {
            return false;
        }

        if (!empty($attributes['block'])) {
            $blockIdentifier = $attributes['block'];
        }

        $methodArgs = array();
        
        $args = (array)$element->children();
        unset($args['@attributes']);
        
        foreach ($args as $argKey => $argument) {
            if ($argument instanceof SimpleXMLElement) {
                $methodArgs[$argKey] = $this->parseArgument($argument);
            } else {
                $methodArgs[$argKey] = $argument;
            }
        }

        if (isset($attributes['json'])) {
            $json = explode(' ', $attributes['json']);
            foreach ($json as $argumentName) {
                $methodArgs[$argumentName] = Mage::helper('core')->jsonDecode($methodArgs[$argumentName]);
            }
        }

        $methodArgs = $this->translateArguments($methodArgs, $element);

        $exportedArgs = array();
        foreach ($methodArgs as $argument) {
            $exportedArgs[] = $this->getExporter()->export($argument);
        }


        $callExpression = sprintf(
            'function ($block) { return $block->%s(%s); }',
            $attributes['method'],
            implode(', ', $exportedArgs)
        );

        $statements = array();
        $statements[] = new Expression(
            sprintf(
                '$this->addItem($item = %s, false)',
                $this->getClassStatement(array(
                    $attributes, $blockIdentifier, new Expression($callExpression), $parentIdentifiers
                ))
            )
        );

        foreach (array_unique(array_merge(array($blockIdentifier), $parentIdentifiers)) as $parentBlock) {
            $statements[] = new Expression(
                sprintf('$this->addItemRelation($item, %s)', var_export($parentBlock, true))
            );
        }

        return $statements;
    }

    /**
     * List of arguments to translate
     *
     * @param array $arguments
     * @param SimpleXMLElement $element
     * @return array
     */
    public function translateArguments($arguments, SimpleXMLElement $element)
    {
        if (!empty($element->attributes()->translate)) {
            $moduleName = (empty($element->attributes()->module) ? 'core' : (string)$element->attributes()->module);
            $itemsToTranslate = explode(' ', (string)$element->attributes()->translate);
            foreach ($itemsToTranslate as $field) {
                $path = explode('.', trim($field));
                $trace = &$arguments;

                while ($path && is_array($trace)) {
                    $levelPath = array_shift($path);
                    if (!isset($trace[$levelPath])) {
                        continue 2; // Breaks to higher level foreach
                    }
                    $trace = &$trace[$levelPath];
                }

                if (!is_array($trace) && empty($path)) {
                    $trace = new EcomDev_LayoutCompiler_Model_Export_Expression_Translate(
                        $moduleName, $trace
                    );
                }
            }
        }

        return $arguments;
    }

    /**
     * Parses arguments from action
     *
     * @param SimpleXMLElement $argument
     * @return array|EcomDev_LayoutCompiler_Model_Export_Expression_Helper|string
     */
    public function parseArgument(SimpleXMLElement $argument)
    {
        if (isset($argument->attributes()->helper)) {
            $helper = (string)$argument->attributes()->helper;
            $info = explode('/', $helper);
            $helperMethod = array_pop($info);
            $helperAlias = implode('/', $info);
            $helperArgs = array();
            if ($this->nodeHasChildren($argument)) {
                $helperArgs = $this->xmlToArray($argument);
            }

            return new EcomDev_LayoutCompiler_Model_Export_Expression_Helper(
                $helperAlias, $helperMethod, $helperArgs
            );
        }

        if ($this->nodeHasChildren($argument)) {
            return $this->xmlToArray($argument);
        }

        return (string)$argument;
    }

    /**
     * Transforms xml to array
     *
     * @param SimpleXMLElement $xml
     * @return string
     */
    private function xmlToArray(SimpleXMLElement $xml)
    {
        $result = array();

        foreach ($xml->children() as $key => $item) {
            if (count($item->children()) > 0) {
                $result[$key] = $this->xmlToArray($item);
            } else {
                $result[$key] = (string)$item;
            }
        }

        return $result;
    }

    /**
     * Check if node has children
     *
     * @param SimpleXMLElement $xml
     * @return bool
     */
    private function nodeHasChildren(SimpleXMLElement $xml)
    {
        return count($xml->children()) > 0;
    }
}
