<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface as MetadataFactoryInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface as ParserInterface;

class EcomDev_LayoutCompiler_Compiler
    implements EcomDev_LayoutCompiler_Contract_CompilerInterface
{
    /**
     * Metadata class
     * 
     * @var MetadataFactoryInterface
     */
    protected $_metadataFactory;

    /**
     * Save path of the compiler
     * 
     * @var string
     */
    protected $_savePath;

    /**
     * List of parsers for a particular node name
     * 
     * @var ParserInterface[]
     */
    protected $_parsers;
    
    /**
     * Constructs a new compiler instance
     * 
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['metadata_factory'])) {
            throw new InvalidArgumentException('Metadata factory is not set in compiler option "metadata_factory"');
        }
        
        if (!$options['metadata_factory'] instanceof MetadataFactoryInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Metadata factory of class "%s" does not implement "%s"', 
                    get_class($options['metadata_factory']),
                    'EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface'
                )
            );
        }
        
        $this->_metadataFactory = $options['metadata_factory'];
        
        if (isset($options['save_path'])) {
            $this->setSavePath($options['save_path']);
        }
        
        if (isset($options['parsers'])) {
            foreach ($options['parsers'] as $key => $value) {
                $this->setParser($key, $value);
            }
        }
    }

    /**
     * Returns current metadata factory instance
     * 
     * @return MetadataFactoryInterface
     */
    public function getMetadataFactory()
    {
        return $this->_metadataFactory;
    }
    
    /**
     * Adds a parser for a compiler for a specific node
     *
     * @param string $nodeName
     * @param ParserInterface $parser
     * @return $this
     */
    public function setParser($nodeName, ParserInterface $parser)
    {
        $this->_parsers[$nodeName] = $parser;
        return $this;
    }

    /**
     * Removes a parser for a specific xml node
     *
     * @param string $nodeName
     * @return ParserInterface
     */
    public function removeParser($nodeName)
    {
        unset($this->_parsers[$nodeName]);
        return $this;
    }

    /**
     * List of parsers 
     * 
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->_parsers;
    }

    /**
     * Compiles a source, if source is not compiled, it should throw a RuntimeException
     *
     * @param SourceInterface $source
     * @param MetadataInterface|null $metadata
     * @return MetadataInterface
     * @throws RuntimeException
     */
    public function compile(SourceInterface $source, MetadataInterface $metadata = null)
    {
        if ($metadata !== null) {
            if ($metadata->validate($source)) {
                return $metadata;
            }
            
            foreach ($metadata->getHandles() as $handleName) {
                @unlink($metadata->getHandlePath($handleName));
            }
        }
        
        $items = $source->load();
        $result = array();
        foreach ($items as $key => $value) {
            $compiled = $this->parseElements($value);
            if ($compiled) {
                $result[$key] = $compiled;
            }
        }
        
        $metadata = $this->_metadataFactory->createFromSource($source, array_keys($result));
        
        foreach ($metadata->getHandles() as $handle) {
            if (isset($result[$handle])) {
                $fileToSave = $metadata->getHandlePath($handle);
                if (!is_dir(dirname($fileToSave))) {
                    mkdir(dirname($fileToSave), 0755, true);
                }
                file_put_contents(
                    $fileToSave, sprintf("<?php return array(\n    %s\n);",  implode(",\n    ", $result[$handle]))
                );
            }
            
        }
        
        return $metadata;
    }

    /**
     * Parses an xml element with one of defined parsers, returns a string
     *
     * @param SimpleXMLElement $element
     * @param string|null $parentIdentifier
     * @return string[]
     */
    public function parseElements(SimpleXMLElement $element, $parentIdentifier = null)
    {
        $result = array();
        foreach ($element->children() as $childName => $childNode) {
            if (!isset($this->_parsers[$childName])) {
                continue;
            }
            $response = $this->_parsers[$childName]
                ->parse($childNode, $this, $parentIdentifier);
            
            if (!$response) {
                continue;
            }
            
            if (!is_array($response)) {
                $response = array($response);
            }
            
            array_splice($result, count($result), 0, $response);
        }
        
        return $result;
    }


    /**
     * Sets a save path for a compiled layout files
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath)
    {
        $this->_savePath = $savePath;
        $this->_metadataFactory->setSavePath($savePath);
        return $this;
    }

    /**
     * Returns a current save path for a compiler
     *
     * @return string
     */
    public function getSavePath()
    {
        return $this->_savePath;
    }
}
