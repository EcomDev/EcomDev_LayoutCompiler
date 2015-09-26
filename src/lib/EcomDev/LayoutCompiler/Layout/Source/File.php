<?php

class EcomDev_LayoutCompiler_Layout_Source_File
    implements EcomDev_LayoutCompiler_Contract_Layout_SourceInterface
{
    /**
     * Path to a source file
     * 
     * @var string
     */
    private $filePath;

    /**
     * Identifier of the source
     * 
     * @var string
     */
    private $id;
    
    /**
     * Returns a file path for a file layout source
     * 
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Validates file path for readability
     * 
     * @throws RuntimeException if something is wrong with the file
     * @return $this
     */
    public function validate()
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException(
                sprintf('File for source does not exists at path "%s"', $this->filePath)
            );
        } elseif (!is_readable($this->filePath)) {
            throw new RuntimeException(
                sprintf('File for source is not readable at path "%s"', $this->filePath)
            );
        }
        
        return $this;
    }
    
    /**
     * Returns array of simple xml objects, where key is a handle name
     *
     * @return SimpleXmlElement[]
     * @throws RuntimeException in case of load error (malformed xml, etc)
     */
    public function load()
    {
        $this->validate();
        
        $original = libxml_use_internal_errors(true);
        $simpleXmlElement = simplexml_load_file($this->filePath);
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
                'File "%s" has a malformed xml structure: %s',
                $this->filePath,
                PHP_EOL . implode(PHP_EOL, $messages)
            ));
        }
        
        $stringXml = array();

        // First convert all elements to string,
        // as in xml file can be multiple string with the same handle names
        foreach ($simpleXmlElement->children() as $key => $element) {
            if (!isset($stringXml[$key])) {
                $stringXml[$key] = '';
            }
            foreach ($element->children() as $child) {
                $stringXml[$key] .= $child->asXml();
            }
        }

        $result = array();
        foreach ($stringXml as $key => $xml) {
            $result[$key] = simplexml_load_string(sprintf('<%1$s>%2$s</%1$s>', $key, $xml));
        }
        
        return $result;
    }

    /**
     * Unique identifier of the layout object
     *
     * @return string
     */
    public function getId()
    {
        if ($this->id === null) {
            $fileName = $this->filePath;
            
            $slash = (strpos($fileName, '\\') === false ? '/' : '\\');
            
            if ($position = strrpos($fileName, '.')) {
                $fileName = substr($fileName, 0, $position);
            }
            
            $paths = explode($slash, $fileName);
            
            if (count($paths) > 4) {
                $paths = array_slice($paths, count($paths) - 4);
            }
            
            $indicator = preg_replace('/[^a-zA-Z0-9_]/', '', implode('_', $paths));
            
            $this->id = sprintf(
                'file_%s_%s',
                $indicator, 
                md5($this->filePath)
            );
        }
        
        return $this->id;
    }

    /**
     * Returns a checksum for a layout source
     *
     * @return string
     */
    public function getChecksum()
    {
        $this->validate();
        return md5_file($this->filePath);
    }

    /**
     * Returns a reference path to an object (e.g. a file path or a string identifier in database)
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->filePath;
    }
}
