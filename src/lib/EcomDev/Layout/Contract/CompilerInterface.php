<?php

use EcomDev_Layout_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_Layout_Contract_Compiler_MetadataInterface as MetadataInterface;
use EcomDev_Layout_Contract_Compiler_ParserInterface as ParserInterface;

/**
 * Compiler instance for a compiler 
 * 
 * 
 */
interface EcomDev_Layout_Contract_CompilerInterface
    extends EcomDev_Layout_Contract_PathAwareInterface
{
    /**
     * Adds a parser for a compiler for a specific node
     * 
     * @param string $nodeName
     * @param ParserInterface $parser
     * @return mixed
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
     * @return MetadataInterface
     */
    public function compile(SourceInterface $source);

    /**
     * Returns a metadata instance for a compiled file if it exists,
     * otherwise it returns false
     * 
     * @param SourceInterface $source
     * @return MetadataInterface
     */
    public function getMetadata(SourceInterface $source);
}
