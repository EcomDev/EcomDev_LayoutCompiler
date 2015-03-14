<?php

/**
 * Class EcomDev_LayoutCompiler_HelperTestTrait
 */
trait EcomDev_LayoutCompiler_HelperTestTrait
{
    private $metadataInterface = 'EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface';
    private $metadataFactoryInterface = 'EcomDev_LayoutCompiler_Contract_Compiler_MetadataFactoryInterface';
    private $parserInterface = 'EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface';
    private $sourceInterface = 'EcomDev_LayoutCompiler_Contract_Layout_SourceInterface';
    
    /**
     * Creates a new xml element
     *
     * @param string $name
     * @param array $data
     * @return SimpleXMLElement
     */
    private function createXmlElement($name, $data)
    {
        $element = new SimpleXMLElement('<' . $name . ' />');
        $generateElement = function (SimpleXMLElement $element, $data, $generator) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $firstKey = key($value);
                    if ($firstKey !== null && is_int($firstKey)) {
                        foreach ($value as $k => $v) {
                            $generator($element->addChild($key), $v, $generator);
                        }
                    } else {
                        $generator($element->addChild($key), $value, $generator);
                    }
                } elseif (strpos($key, '@') === 0) {
                    $element->addAttribute(substr($key, 1), htmlentities($value));
                } else {
                    $element->addChild($key, htmlentities($value));
                }
            }
        };

        $generateElement($element, $data, $generateElement);
        return $element;
    }

    /***
     * Return parser instance
     *
     * @param PHPUnit_Framework_MockObject_Stub|string|int|null $map
     * @return PHPUnit_Framework_MockObject_MockObject|EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface
     */
    private function createParser($map = null)
    {
        if ($map instanceof PHPUnit_Framework_MockObject_Stub) {
            $return = $map;
        } else {
            $return = $this->returnValue($map);
        }

        $parser = $this->getMockForAbstractClass($this->parserInterface);

        if ($map !== null) {
            $parser->expects($this->any())
                ->method('parse')
                ->will($return);
        }

        return $parser;
    }

    /***
     * Return parser instance
     *
     * @param array $handles
     * @param bool $count
     * @return PHPUnit_Framework_MockObject_MockObject|EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface
     */
    private function createMetadata($handles = array(), $count = false)
    {
        $parser = $this->getMockForAbstractClass($this->metadataInterface);

        $parser->expects($count ? $this->once() : $this->any())
            ->method('getHandles')
            ->willReturn(array_keys($handles));

        if ($count) {
            $handlesMatcher = count($handles) ? $this->exactly(count($handles)) : $this->never();
        } else {
            $handlesMatcher = $this->any();
        }

        $parser->expects($handlesMatcher)
            ->method('getHandlePath')
            ->with($this->isType('string'))
            ->willReturnCallback(function ($handleName) use ($handles) {
                return $handles[$handleName];
            });

        return $parser;
    }

    /***
     * Return parser instance
     *
     * @param array|bool $loadResult
     * @return PHPUnit_Framework_MockObject_MockObject|EcomDev_LayoutCompiler_Contract_Layout_SourceInterface
     */
    private function createSource($loadResult = array())
    {
        $source = $this->getMockForAbstractClass($this->sourceInterface);

        if ($loadResult === false) {
            $source->expects($this->never())
                ->method('load');
        } else {
            foreach ($loadResult as $handleName => $items) {
                if (is_array($items)) {
                    $loadResult[$handleName] = $this->createXmlElement($handleName, $items);
                }
            }

            $source->expects($this->once())
                ->method('load')
                ->willReturn($loadResult);
        }

        return $source;
    }
}