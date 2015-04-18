<?php

/**
 * Database source for a layout
 *
 */
class EcomDev_LayoutCompiler_Model_Layout_Source_Database
    implements EcomDev_LayoutCompiler_Contract_Layout_SourceInterface
{
    /**
     * Area name
     *
     * @var string
     */
    private $area;

    /**
     * Package name
     *
     * @var string
     */
    private $package;

    /**
     * Theme name
     *
     * @var string
     */
    private $theme;

    /**
     * Store id for a layout update lookup
     *
     * @var int
     */
    private $storeId;

    /**
     * Configures database source with options passed in
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->area = isset($options['area']) ? $options['area'] : '';
        $this->package = isset($options['package']) ? $options['package'] : '';
        $this->theme = isset($options['theme']) ? $options['theme'] : '';
        $this->storeId = (int)(isset($options['store_id']) ? $options['store_id'] : 0);
    }


    /**
     * Returns array of simple xml objects, where key is a handle name
     *
     * @return SimpleXmlElement[]
     * @throws RuntimeException in case of load error (malformed xml, etc)
     */
    public function load()
    {
        $handles = $this->getResource()->getLayoutUpdateHandles(
            $this->area, $this->package, $this->theme, $this->storeId
        );

        $result = array();

        foreach ($handles as $handleName => $xml) {
            $result[$handleName] = $this->loadXml($xml, $handleName);
        }

        return $result;
    }

    /**
     * @param $xmlString
     * @param $handleName
     * @return SimpleXMLElement
     */
    private function loadXml($xmlString, $handleName)
    {
        $xmlString = sprintf('<%1$s>%2$s</%1$s>', $handleName, $xmlString);

        $original = libxml_use_internal_errors(true);
        $simpleXmlElement = simplexml_load_string($xmlString);
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
                    "A database handle \"%s\" for %s area and %s/%s theme has a malformed xml structure:\n%s\nContent: %s",
                    $handleName,
                    $this->area,
                    $this->package,
                    $this->theme,
                    implode(PHP_EOL, $messages),
                    $xmlString
                )
            );
        }

        return $simpleXmlElement;
    }

    /**
     * Unique identifier of the layout object
     *
     * @return string
     */
    public function getId()
    {
        return sprintf(
            'db_%s_%s_%s_store_%s',
            $this->area,
            $this->package,
            $this->theme,
            $this->storeId
        );
    }

    /**
     * Returns a checksum for a layout source
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->getResource()->getLayoutUpdateChecksum(
            $this->area, $this->package, $this->theme, (int)$this->storeId
        );
    }

    /**
     * Returns a reference path to an object (e.g. a file path or a string identifier in database)
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return sprintf(
            'db_%s_%s_%s_store_%s',
            $this->area,
            $this->package,
            $this->theme,
            $this->storeId
        );
    }

    /**
     * Returns an instance of resource model
     *
     * @return EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('ecomdev_layoutcompiler/layout_source_database');
    }
}
