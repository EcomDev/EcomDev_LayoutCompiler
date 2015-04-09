<?php

class EcomDev_LayoutCompiler_AbstractFactoryTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_AbstractFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_AbstractFactory');
    }

    public function testItSetsAliasIntoPropertyForLaterUsage()
    {
        $this->assertSame(
            $this->factory,
            $this->factory->setClassAlias('alias_one', 'ClassName')
        );

        $this->assertSame(
            $this->factory,
            $this->factory->setClassAlias('alias_two', 'alias_one')
        );

        $this->assertAttributeSame(
            array(
                'alias_one' => 'ClassName',
                'alias_two' => 'alias_one'
            ),
            'classAlias',
            $this->factory
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Alias "reference_one" is referencing itself in stack of "reference_one->reference_two->reference_three"
     */
    public function testItThrowsARuntimeExceptionIfAliasIsInRecursion()
    {
        $this->factory->setClassAlias('reference_one', 'reference_two')
                ->setClassAlias('reference_two', 'reference_three')
                ->setClassAlias('reference_three', 'reference_one');

        $this->factory->resolveAlias('reference_one');
    }

    public function testItResolvesAliases()
    {
        $this->factory->setClassAlias('reference_one', 'reference_two')
            ->setClassAlias('reference_two', 'reference_three')
            ->setClassAlias('reference_four', 'reference_five')
        ;

        $this->assertSame('reference_three', $this->factory->resolveAlias('reference_one'));
        $this->assertSame('reference_five', $this->factory->resolveAlias('reference_four'));
    }

    public function testItResolvesAliasAsAnArgumentIfNoAliasesDefined()
    {
        $this->assertSame('non_reference', $this->factory->resolveAlias('non_reference'));
    }

    public function testItSetsDefaultArgumentsForConstructor()
    {
        $this->assertSame(
            $this->factory,
            $this->factory->setDefaultConstructorArguments('reference_one', array('argument_one', 'argument_two'))
        );

        $this->assertAttributeSame(
            array('reference_one' => array('argument_one', 'argument_two')),
            'defaultConstructorArguments',
            $this->factory
        );
    }

    public function testItRetrievesDefaultArgumentsViaAlias()
    {
        $this->factory->setDefaultConstructorArguments('class_one', array('argument_one', 'argument_two'));
        $this->factory->setClassAlias('reference_one', 'class_one');

        $this->assertSame(
            array('argument_one', 'argument_two'),
            $this->factory->getDefaultConstructorArguments('reference_one')
        );
    }

    public function testItRetrievesDefaultArgumentsForAlias()
    {
        $this->factory->setDefaultConstructorArguments('reference_one', array('argument_one', 'argument_two'));
        $this->factory->setClassAlias('reference_one', 'class_one');

        $this->assertSame(
            array('argument_one', 'argument_two'),
            $this->factory->getDefaultConstructorArguments('reference_one')
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Alias "reference_one" is referencing itself in stack of "reference_one->reference_two->reference_three"
     */
    public function testItThrowsARuntimeExceptionIfAliasIsInRecursionForConstructorArguments()
    {
        $this->factory->setClassAlias('reference_one', 'reference_two')
            ->setClassAlias('reference_two', 'reference_three')
            ->setClassAlias('reference_three', 'reference_one');

        $this->factory->getDefaultConstructorArguments('reference_one');
    }

    public function testItRetrievesDefaultArgumentsForConstructorIfDataValuesIsProvided()
    {
        $this->factory->setClassAlias('reference_one', 'reference_two')
            ->setClassAlias('reference_two', 'reference_three');

        $this->assertSame(
            array(),
            $this->factory->getDefaultConstructorArguments('reference_one')
        );
    }

    public function testItAddsDependencyInjectionInstruction()
    {
        $this->assertSame(
            $this->factory,
            $this->factory->setDependencyInjectionInstruction(
                'EcomDev_LayoutCompiler_Contract_FactoryAwareInterface',
                'setObjectFactory',
                $this->factory
            )
        );

        $this->assertAttributeSame(
            array(
                'EcomDev_LayoutCompiler_Contract_FactoryAwareInterface' => array('setObjectFactory', $this->factory)
            ),
            'dependencyInjectionInstruction',
            $this->factory
        );
    }

    public function testItCreatesANewInstanceOfClassWithSpecifiedArguments()
    {
        $this->configureCreateInstance('EcomDev_LayoutCompiler_FactoryFixture_ClassOne');
        $instance = $this->factory->createInstance('class', 'value1', 'value2', 'value3');
        $this->assertInstanceOf('EcomDev_LayoutCompiler_FactoryFixture_ClassOne', $instance);
        $this->assertAttributeSame(array('value1', 'value2', 'value3'), 'arguments', $instance);
    }

    public function testItTakesDefaultArgumentsForInstanceIfNoArgumentsSpecified()
    {
        $this->configureCreateInstance('EcomDev_LayoutCompiler_FactoryFixture_ClassOne');
        $instance = $this->factory->createInstance('class');
        $this->assertInstanceOf('EcomDev_LayoutCompiler_FactoryFixture_ClassOne', $instance);
        $this->assertAttributeSame(array('value3', 'value4', 'value5'), 'arguments', $instance);
    }

    public function testItCallsMethodDefinedAsDependencyInjectionSetter()
    {
        $this->configureCreateInstance('EcomDev_LayoutCompiler_FactoryFixture_ClassTwo');
        $instance = $this->factory->createInstance('class');
        $this->assertInstanceOf('EcomDev_LayoutCompiler_FactoryFixture_ClassTwo', $instance);
        $this->assertAttributeSame($this->factory, 'someData', $instance);
    }

    private function configureCreateInstance($className)
    {
        // Class alias
        $this->factory->setClassAlias('class', $className);
        // Default arguments
        $this->factory->setDefaultConstructorArguments('class', array('value3', 'value4', 'value5'));
        // Dependency injection via setter
        $this->factory->setDependencyInjectionInstruction(
            'EcomDev_LayoutCompiler_FactoryFixture_OneInterface',
            'setSomeData',
            $this->factory
        );

        $this->factory->expects($this->once())
            ->method('resolveClassName')
            ->with($className)
            ->willReturnArgument(0);

        return $this;
    }
}
