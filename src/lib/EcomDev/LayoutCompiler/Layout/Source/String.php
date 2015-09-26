<?php

/**
 * String layout source
 * 
 * 
 */
class EcomDev_LayoutCompiler_Layout_Source_String
    implements EcomDev_LayoutCompiler_Contract_Layout_SourceInterface
{
    /**
     * Content of xml layout update
     * 
     * @var string
     */
    private $content;
    
    /**
     * Creates a new source based on string source 
     * 
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }
    
    /**
     * Returns array of simple xml objects, where key is a handle name
     *
     * @return SimpleXmlElement[]
     * @throws RuntimeException in case of load error (malformed xml, etc)
     */
    public function load()
    {
        $xml = sprintf('<layout>%s</layout>', $this->content);
        $original = libxml_use_internal_errors(true);
        $simpleXmlElement = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($original);
        
        if ($simpleXmlElement === false) {
            $messages = array();

            foreach ($errors as $error) {
                $messages[] = sprintf(
                    '%s, line %s, column %s',
                    trim($error->message),
                    $error->line,
                    $error->column
                );
            }

            throw new RuntimeException(sprintf(
                "String source has a malformed structure:\n%s\nContent: %s",
                implode(PHP_EOL, $messages),
                $xml
            ));
        }
        
        return array(
            $this->getId() => $simpleXmlElement
        );
    }

    /**
     * Unique identifier of the layout object
     *
     * @return string
     */
    public function getId()
    {
        return sprintf('string_%s', $this->getChecksum());
    }

    /**
     * Returns a checksum for a layout source
     *
     * @return string
     */
    public function getChecksum()
    {
        return md5($this->content);
    }

    /**
     * Returns a reference path to an object (e.g. a file path or a string identifier in database)
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->getId();
    }
}
