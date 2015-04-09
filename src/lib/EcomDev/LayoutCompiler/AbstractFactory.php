<?php

/**
 * Abstract factory implementation
 * 
 * Requires only to implement resolveClassName() method,
 * that will find a correct class name via your own service locator
 */
abstract class EcomDev_LayoutCompiler_AbstractFactory
    implements EcomDev_LayoutCompiler_Contract_FactoryInterface
{
    /**
     * Aliases of the class names
     *
     * @var string[]
     */
    private $classAlias = array();

    /**
     * Default arguments for creating an object via factory
     *
     * @var mixed[][]
     */
    private $defaultConstructorArguments = array();

    /**
     * Cache of reflection objects
     *
     * @var ReflectionClass[]
     */
    private $reflectionCache = array();

    /**
     * Alias cache
     *
     * @var string[][]
     */
    private $aliasCache = array();

    /**
     * Dependency injection
     *
     * @var mixed[][]
     */
    private $dependencyInjectionInstruction = array();

    /**
     * Resolves a class name via internal service locator
     *
     * @param string $className
     * @return string|bool
     */
    abstract protected function resolveClassName($className);

    /**
     * Creates a new instance of class, if no arguments are supplied except a class name,
     * it will pick up the default ones from configuration
     *
     * @param string $aliasOrClassName
     * @param mixed $argument,... unlimited OPTIONAL list of class constructor arguments
     * @return object
     * @throws RuntimeException in case if class cannot be found or there is a recursion
     */
    public function createInstance($aliasOrClassName, $argument = null)
    {
        $arguments = func_get_args();
        $aliasOrClassName = array_shift($arguments);

        if ($argument === null && count($arguments) < 1) {
            $arguments = array();
        }

        $className = $this->resolveClassName($this->resolveAlias($aliasOrClassName));

        if (empty($arguments)) {
            $arguments = $this->getDefaultConstructorArguments($aliasOrClassName);
        }

        $reflection = $this->getReflectionClass($className);
        $instance = $reflection->newInstanceArgs($arguments);

        foreach ($this->dependencyInjectionInstruction as $interface => $call) {
            if ($instance instanceof $interface) {
                $instance->{$call[0]}($call[1]);
            }
        }

        return $instance;
    }


    private function getReflectionClass($className)
    {
        if (!isset($this->reflectionCache[$className])) {
            $this->reflectionCache[$className] = new ReflectionClass($className);
        }

        return $this->reflectionCache[$className];
    }

    /**
     * Sets an instruction for a dependency injection for items created via factory
     * that implement specified interface
     * 
     * Every call to this method with the same $interface will override previous call
     *
     * @param string $interface
     * @param string $method
     * @param mixed $argument
     * @return $this
     */
    public function setDependencyInjectionInstruction($interface, $method, $argument)
    {
        $this->dependencyInjectionInstruction[$interface] = array($method, $argument);
        return $this;
    }

    /**
     * Sets default construction arguments for a class or an alias
     * If alias is provided, instead of fully qualified class name, it will use its arguments instead of class one
     *
     * @param string $aliasOrClassName
     * @param array $arguments
     * @return $this
     */
    public function setDefaultConstructorArguments($aliasOrClassName, array $arguments)
    {
        $this->defaultConstructorArguments[$aliasOrClassName] = $arguments;
        return $this;
    }

    /**
     * Class name alias, for easier resource creation
     *
     * @param string $alias
     * @param string $className
     * @return $this
     */
    public function setClassAlias($alias, $className)
    {
        $this->classAlias[$alias] = $className;
        return $this;
    }

    /**
     * Resolves an alias
     *
     * @param string $aliasOrClassName
     * @throws RuntimeException if alias is in recursion
     * @return string
     */
    public function resolveAlias($aliasOrClassName)
    {
        return $this->resolveAliasWithCache($aliasOrClassName, 'class');
    }

    /**
     * Returns constructor arguments for alias or class name
     *
     * @param string $aliasOrClassName
     * @return mixed[]
     */
    public function getDefaultConstructorArguments($aliasOrClassName)
    {
        $alias = $this->resolveAliasWithCache($aliasOrClassName, 'arguments', $this->defaultConstructorArguments);

        if (isset($this->defaultConstructorArguments[$alias])) {
            return $this->defaultConstructorArguments[$alias];
        }

        return array();
    }

    /**
     * Resolve alias with cache
     *
     * @param string $aliasOrClassName
     * @param string $cacheKey
     * @param string[] $additionalCheck
     * @return string
     * @throws RuntimeException
     */
    private function resolveAliasWithCache($aliasOrClassName, $cacheKey, array $additionalCheck = array())
    {
        $alias = $aliasOrClassName;

        if (!isset($this->aliasCache[$cacheKey][$aliasOrClassName])) {
            $stack = array();
            while (!isset($additionalCheck[$alias]) && isset($this->classAlias[$alias])) {
                $stack[$alias] = true;
                $alias = $this->classAlias[$alias];
                if (isset($stack[$alias])) {
                    throw new RuntimeException(
                        sprintf(
                            'Alias "%s" is referencing itself in stack of "%s"',
                            $alias,
                            implode('->', array_keys($stack))
                        )
                    );
                }
            }

            $this->aliasCache[$cacheKey][$aliasOrClassName] = $alias;
        }

        return $this->aliasCache[$cacheKey][$aliasOrClassName];
    }
}
