<?php

use EcomDev_LayoutCompiler_Contract_Layout_SourceInterface as SourceInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface as MetadataInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface as MetadataFactoryInterface;
use EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface as ParserInterface;
use EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface as ExpressionInterface;
use Symfony\Component\Filesystem\Filesystem;

class EcomDev_LayoutCompiler_Compiler
    implements EcomDev_LayoutCompiler_Contract_CompilerInterface
{
    use EcomDev_LayoutCompiler_PathAwareTrait {
        setSavePath as private traitSetSavePath;
    }
    
    /**
     * Metadata class
     * 
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * List of parsers for a particular node name
     * 
     * @var ParserInterface[]
     */
    protected $parsers;

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
        
        $this->metadataFactory = $options['metadata_factory'];
        
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
        return $this->metadataFactory;
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
        $this->parsers[$nodeName] = $parser;
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
        unset($this->parsers[$nodeName]);
        return $this;
    }

    /**
     * List of parsers 
     * 
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
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
        
        $metadata = $this->metadataFactory->createFromSource($source, array_keys($result));
        
        foreach ($metadata->getHandles() as $handle) {
            if (isset($result[$handle])) {
                $fileToSave = $metadata->getHandlePath($handle);
                $path = dirname($fileToSave);
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                $tmpFile = $path . DIRECTORY_SEPARATOR . uniqid('tempfile', TRUE);
                $content = sprintf(
                    "<?php %s",
                    implode(
                        "\n",
                        array_map(function ($item) {
                            if ($item instanceof ExpressionInterface) {
                                return sprintf('%s;', (string)$item);
                            }

                            return sprintf('$this->addItem(%s);', $item);
                        }, $result[$handle])
                    )
                );

                file_put_contents($tmpFile, $content);

                rename($tmpFile, $fileToSave);
                chmod($fileToSave, 0644);
            }
            
        }
        
        return $metadata;
    }

    /**
     * Parses an xml element with one of defined parsers, returns a string
     *
     * @param SimpleXMLElement $element
     * @param string|null $blockIdentifier
     * @param string[] $parentIdentifiers
     * @return string[]
     */
    public function parseElements(SimpleXMLElement $element, $blockIdentifier = null, array $parentIdentifiers = array())
    {
        $result = array();
        foreach ($element->children() as $childName => $childNode) {
            if (!isset($this->parsers[$childName])) {
                continue;
            }
            
            $response = $this->parsers[$childName]
                ->parse($childNode, $this, $blockIdentifier, $parentIdentifiers);
            
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
        $this->traitSetSavePath($savePath);
        $this->metadataFactory->setSavePath($this->getSavePath());
        return $this;
    }
}
