<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface as ParserInterface;

/**
 * Compiler instance for a compiler 
 * 
 * 
 */
interface EcomDev_LayoutCompiler_Contract_CompilerInterface
    extends EcomDev_LayoutCompiler_Contract_PathAwareInterface
{
    /**
     * Adds a parser for a compiler for a specific node
     * 
     * @param string $nodeName
     * @param ParserInterface $parser
     * @return $this
     */
    public function setParser($nodeName, ParserInterface $parser);

    /**
     * Removes a parser for a specific xml node 
     *
     * @param string $nodeName
     * @return ParserInterface
     */
    public function removeParser($nodeName);

    /**
     * Compiles a source, if source is not compiled, it should throw a RuntimeException
     * 
     * @param SourceInterface $source
     * @param MetadataInterface|null $metadata
     * @return MetadataInterface
     */
    public function compile(SourceInterface $source, MetadataInterface $metadata = null);

    /**
     * Parses an xml element with one of defined parsers
     * 
     * @param SimpleXMLElement $element
     * @param string|null $blockIdentifier
     * @param string[] $parentIdentifiers
     * @return string[]
     */
    public function parseElements(SimpleXMLElement $element, $blockIdentifier = null, array $parentIdentifiers = array());
}
