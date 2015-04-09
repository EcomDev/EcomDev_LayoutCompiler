<?php

/**
 * Objects creation factory
 * 
 * Creates an instance of particular object by using configuration values supplied to it
 */
interface EcomDev_LayoutCompiler_Contract_FactoryInterface
{
    /**
     * Creates a new instance of class, if no arguments are supplied except a class name,
     * it will pick up the default ones from configuration
     *
     * @param string $aliasOrClassName
     * @param mixed $argument,... unlimited OPTIONAL list of class constructor arguments
     * @return object
     * @throws RuntimeException in case if class cannot be found
     */
    public function createInstance($aliasOrClassName, $argument = null);
    
    /**
     * Sets an instruction for a dependency injection for items created via factory
     * that implement specified interface
     *
     * @param string $interface
     * @param string $method
     * @param mixed $argument
     * @return $this
     */
    public function setDependencyInjectionInstruction($interface, $method, $argument);

    /**
     * Sets default construction arguments for a class or an alias
     * If alias is provided, instead of fully qualified class name, it will use its arguments instead of class one 
     * 
     * @param string $aliasOrClassName
     * @param array $arguments
     * @return $this
     */
    public function setDefaultConstructorArguments($aliasOrClassName, array $arguments);

    /**
     * Returns constructor arguments for alias or class name
     *
     * @param string $aliasOrClassName
     * @return mixed[]
     */
    public function getDefaultConstructorArguments($aliasOrClassName);

    /**
     * Class name alias, for easier resource creation
     * 
     * @param string $alias
     * @param string $className
     * @return $this
     */
    public function setClassAlias($alias, $className);
}
