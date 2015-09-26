<?php
use EcomDev_PHPUnit_Test_Case_Util as TestUtil;

class EcomDev_LayoutCompilerTest_Test_Model_Layout_Source_DatabaseTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Source instance under test
     *
     * @var EcomDev_LayoutCompiler_Model_Layout_Source_Database
     */
    private $source;

    protected function setUp()
    {
        $options = array();

        $options['area'] = current(TestUtil::getAnnotationByNameFromClass(
            __CLASS__, 'area', 'method', $this->getName(false)
        ));
        $options['package'] = current(TestUtil::getAnnotationByNameFromClass(
            __CLASS__, 'package', 'method', $this->getName(false)
        ));
        $options['theme'] = current(TestUtil::getAnnotationByNameFromClass(
            __CLASS__, 'theme', 'method', $this->getName(false)
        ));
        $options['store_id'] = current(TestUtil::getAnnotationByNameFromClass(
            __CLASS__, 'storeId', 'method', $this->getName(false)
        ));

        $this->source = new EcomDev_LayoutCompiler_Model_Layout_Source_Database($options);
    }

    public function testItReturnsSourceDatabaseResourceModelOnGetResourceCall()
    {
        $originalResource = $this->source->getResource();
        $this->assertInstanceOf(
            'EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database',
            $originalResource
        );

        $resourceMock = $this->mockResourceModel('ecomdev_layoutcompiler/layout_source_database')
            ->replaceByMock('resource_singleton');

        $this->assertSame($resourceMock->getMockInstance(), $this->source->getResource());
    }

    /**
     * @area frontend
     * @package base
     * @theme default
     * @storeId 3
     */
    public function testItReturnsIdentifierBasedOnPassedConstructorArguments()
    {
        $this->assertSame('db_frontend_base_default_store_3', $this->source->getId());
    }

    /**
     * @area frontend
     * @package base
     * @theme default
     * @storeId 3
     */
    public function testItReturnsChecksumFromADatabaseResource()
    {
        $resourceMock = $this->mockResourceModel('ecomdev_layoutcompiler/layout_source_database')
            ->setMethods(array('getLayoutUpdateChecksum'))
            ->replaceByMock('resource_singleton');

        $resourceMock->expects($this->once())
            ->method('getLayoutUpdateChecksum')
            ->with('frontend', 'base', 'default', 3)
            ->willReturn('checksum_from_db_method');

        $this->assertSame('checksum_from_db_method', $this->source->getChecksum());
    }

    /**
     * @area frontend
     * @package base
     * @theme default
     * @storeId 3
     */
    public function testItReturnsOriginalPathTheSameAsIdentifier()
    {
        $this->assertSame('db_frontend_base_default_store_3', $this->source->getOriginalPath());
    }

    /**
     * @area frontend
     * @package base
     * @theme default
     * @storeId 3
     */
    public function testItReturnsSimpleXmlElementsBasedOnDataReturnedFromDatabase()
    {
        $resourceMock = $this->mockResourceModel('ecomdev_layoutcompiler/layout_source_database')
            ->setMethods(array('getLayoutUpdateHandles'))
            ->replaceByMock('resource_singleton');

        $resourceMock->expects($this->once())
            ->method('getLayoutUpdateHandles')
            ->with('frontend', 'base', 'default', 3)
            ->willReturn(array('handle_one' => '<item2 /><item3 />', 'handle_two' => ''));

        $result = $this->source->load();
        $this->assertSame(array('handle_one', 'handle_two'), array_keys($result));

        $this->assertXmlStringEqualsXmlString(
            '<handle_one><item2 /><item3/></handle_one>',
            $result['handle_one']->asXML()
        );
        $this->assertXmlStringEqualsXmlString(
            '<handle_two></handle_two>',
            $result['handle_two']->asXML()
        );
    }

    /**
     * @area frontend
     * @package base
     * @theme default
     * @storeId 3
     * @expectedException RuntimeException
     * @expectedExceptionMessage A database handle "handle_one" for frontend area and base/default theme has a malformed xml structure:
     */
    public function testItThrowsAnExceptionWhenLoadOfDataFails()
    {
        $resourceMock = $this->mockResourceModel('ecomdev_layoutcompiler/layout_source_database')
            ->setMethods(array('getLayoutUpdateHandles'))
            ->replaceByMock('resource_singleton');

        $resourceMock->expects($this->once())
            ->method('getLayoutUpdateHandles')
            ->with('frontend', 'base', 'default', 3)
            ->willReturn(array('handle_one' => '<item2><item1>', 'handle_two' => '<item1 />'));

        $this->source->load();
    }
}
