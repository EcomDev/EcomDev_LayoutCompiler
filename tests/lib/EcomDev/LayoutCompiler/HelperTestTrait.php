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
    private $layoutInterface = 'EcomDev_LayoutCompiler_Contract_LayoutInterface';
    private $layoutItemInterface = 'EcomDev_LayoutCompiler_Contract_Layout_ItemInterface';
    private $layoutItemBlockAware = 'EcomDev_LayoutCompiler_Layout_Item_AbstractBlockItem';
    private $compilerInterface = 'EcomDev_LayoutCompiler_Contract_CompilerInterface';
    private $processorInterface = 'EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface';
    private $loaderInterface = 'EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface';
    private $updateInterface = 'EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface';
    private $indexInterface = 'EcomDev_LayoutCompiler_Contract_IndexInterface';
    private $errorProcessorInterface = 'EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface';
    
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
     * @param bool|int $countHandles
     * @param bool $countHandlePath
     * @return PHPUnit_Framework_MockObject_MockObject|EcomDev_LayoutCompiler_Contract_Compiler_MetadataInterface
     */
    private function createMetadata($handles = array(), $countHandles = false, $countHandlePath = true)
    {
        $parser = $this->getMockForAbstractClass($this->metadataInterface);

        $getHandlesMatcher = $countHandles ? 
            (is_int($countHandles) ?  $this->exactly($countHandles) :  $this->once() ) : 
            $this->any();
        
        $parser->expects($getHandlesMatcher)
            ->method('getHandles')
            ->willReturn(array_keys($handles));

        if ($countHandles && $countHandlePath) {
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

    /**
     * Creates an instance of error processor
     * 
     * @param EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface|null $forObject
     * @return EcomDev_LayoutCompiler_Contract_ErrorProcessorInterface
     */
    private function createErrorProcessor($forObject = null)
    {
        $errorProcessor = $this->getMockForAbstractClass($this->errorProcessorInterface);
        
        if ($forObject !== null 
            && $forObject instanceof EcomDev_LayoutCompiler_Contract_ErrorProcessorAwareInterface) {
            $forObject->addErrorProcessor($errorProcessor);
        }
        
        return $errorProcessor;
    }

    /**
     * Annotation by name
     * 
     * @param string $name
     * @return string|string[]|null
     */
    private function getAnnotationByName($name, $single = true)
    {
        if ($this instanceof PHPUnit_Framework_TestCase) {
            $annotations = $this->getAnnotations();
            if (isset($annotations['method'][$name])) {
                return $single ? 
                    $annotations['method'][$name][0] :
                    $annotations['method'][$name];
            }
        }
        
        return $single ? null : array();
    }

    /**
     * Returns a mock of update interface
     *
     * @return EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createUpdate()
    {
        return $this->getMockForAbstractClass($this->updateInterface);
    }

    /**
     * Returns a mock of index interface
     *
     * @return EcomDev_LayoutCompiler_Contract_IndexInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createIndex()
    {
        return $this->getMockForAbstractClass($this->indexInterface);
    }

    /**
     * Returns a mock of processor interface
     *
     * @return EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createProcessor()
    {
        return $this->getMockForAbstractClass($this->processorInterface);
    }

    /**
     * Returns a mock of loader interface
     *
     * @return EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createLoader()
    {
        return $this->getMockForAbstractClass($this->loaderInterface);
    }

    /**
     * Returns a mock of compiler interface
     * 
     * @return EcomDev_LayoutCompiler_Contract_CompilerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createCompiler()
    {
        return $this->getMockForAbstractClass($this->compilerInterface);
    }

    /**
     * Returns a mock of compiler interface
     *
     * @param null|int $type
     * @param int $countItem
     * @return EcomDev_LayoutCompiler_Contract_Layout_ItemInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createLayoutItem($type = null, $countItem = 1)
    {
        $item = $this->getMockForAbstractClass($this->layoutItemInterface);
        if ($type !== null) {
            $item->expects($this->exactly($countItem))
                ->method('getType')
                ->willReturn($type);
        }
        return $item;
    }

    /**
     * Returns a mock of compiler interface
     *
     * @param array $arguments
     * @return EcomDev_LayoutCompiler_Contract_Layout_ItemInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createBlockAwareLayoutItem(array $arguments = array())
    {
        return $this->getMockForAbstractClass($this->layoutItemBlockAware, $arguments);
    }


    /**
     * Creates a layout mock based on interface
     * 
     * If you 
     * 
     * @return EcomDev_LayoutCompiler_Contract_LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createLayout()
    {
        $layout = $this->getMockForAbstractClass($this->layoutInterface);
        
        $this->stubObjectMethods(
            func_get_args(),
            $layout,
            array(
                $this->compilerInterface => 'getCompiler',
                $this->processorInterface => 'getProcessor',
                $this->loaderInterface => 'getLoader',
                $this->updateInterface => 'getUpdate'
            )
        );
        
        return $layout;
    }
    
    private function stubObjectMethods($values, PHPUnit_Framework_MockObject_MockObject $object, $map)
    {
        foreach ($values as $value) {
            foreach ($map as $interface => $method) {
                if ($value instanceof $interface) {
                    $object->expects($this->any())
                        ->method($method)
                        ->willReturn($value);
                    unset($map[$interface]); // Remove interface from map, so no next value is matched
                }
            }
        }
        
        return $this;
    }

    /**
     * Writes an attribute to object
     * 
     * @param object $object
     * @param string $attribute
     * @param mixed $value
     * @return $this
     */
    private function writeAttribute($object, $attribute, $value)
    {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty($attribute);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
        return $this;
    }
}