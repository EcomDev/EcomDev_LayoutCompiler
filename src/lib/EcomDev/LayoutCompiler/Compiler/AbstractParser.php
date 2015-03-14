<?php

use EcomDev_LayoutCompiler_Contract_Exporter_ExpressionInterface as ExpressionInterface;
use EcomDev_LayoutCompiler_Contract_ExporterInterface as ExporterInterface;

abstract class EcomDev_LayoutCompiler_Compiler_AbstractParser
    implements EcomDev_LayoutCompiler_Contract_Compiler_ParserInterface, 
               EcomDev_LayoutCompiler_Contract_ExporterAwareInterface
{
    /**
     * Class name that should be used 
     * for generation of parser statement output
     * 
     * @var string
     */
    private $className;

    /**
     * Exporter that is used to transform values into php code 
     * 
     * @var ExporterInterface
     */
    private $exporter;
    
    /**
     * Returns a class name, that parser will return
     * 
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Sets a class name for parser output
     * 
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $requiredInterface = 'EcomDev_LayoutCompiler_Contract_Layout_ItemInterface';
        $implementedInterfaces = class_implements($className, true);
        if (!is_array($implementedInterfaces) || !in_array($requiredInterface, $implementedInterfaces)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" should implement %s', $className, $requiredInterface)
            );
        }
        
        $this->className = $className;
        return $this;
    }

    /**
     * Returns a class statement
     * 
     * @param mixed[] $arguments
     * @return string
     */
    public function getClassStatement(array $arguments)
    {
        foreach ($arguments as $index => $argument) {
            $arguments[$index] = $this->exporter->export($argument);
        }
        
        return sprintf('new %s(%s)', $this->getClassName(), implode(', ', $arguments));
    }

    /**
     * Sets an exporter into object
     *
     * @param ExporterInterface $exporter
     * @return string
     */
    public function setExporter(ExporterInterface $exporter)
    {
        $this->exporter = $exporter;
        return $this;
    }

    /**
     * Returns an exporter for an object
     *
     * @return ExporterInterface
     */
    public function getExporter()
    {
        return $this->exporter;
    }
}