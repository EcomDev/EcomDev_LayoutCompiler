<?php

class EcomDev_LayoutCompilerTest_Test_Model_Resource_Layout_Source_DatabaseTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database
     */
    private $resource;

    protected function setUp()
    {
        $this->resource = new EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database();
    }

    public function testItHasLayoutUpdateSetAsMainTable()
    {
        $this->assertSame('core_layout_update', $this->resource->getMainTable());
        $this->assertSame('layout_update_id', $this->resource->getIdFieldName());
    }

    /**
     * Expected checksum value for a theme
     *
     * @param string $expectedChecksum
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param int $storeId
     * @dataProvider dataProviderThemeChecksum
     * @loadFixture layout-updates
     */
    public function testItReturnsChecksumForSpecifiedTheme($expectedChecksum, $area, $package, $theme, $storeId)
    {
        $this->assertSame($expectedChecksum, $this->resource->getLayoutUpdateChecksum(
            $area, $package, $theme, $storeId
        ));
    }

    public function dataProviderThemeChecksum()
    {
        return [
            ['6211dc96809db042072965e80123c8ac', 'frontend', 'default', 'default', '1'],
            ['9e0ab73e2de1f01747d6b59932d4ba47', 'frontend', 'default', 'default', '0'],
            ['b13bb4f6e238e80b420b9995bfd1fa8a', 'frontend', 'default', 'blue', '1'],
            ['9e0ab73e2de1f01747d6b59932d4ba47', 'frontend', 'default', 'default', '2'],
            ['d41d8cd98f00b204e9800998ecf8427e', 'frontend', 'my_package', 'default', '1'],
        ];
    }

    /**
     * Expected checksum value for a theme
     *
     * @param string[] $expectedResult
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param int $storeId
     * @dataProvider dataProviderThemeUpdates
     * @loadFixture layout-updates
     */
    public function testItReturnsArrayOfLayoutUpdatesForSpecifiedTheme(
        $expectedResult, $area, $package, $theme, $storeId
    )
    {
        $this->assertSame($expectedResult, $this->resource->getLayoutUpdateHandles(
            $area, $package, $theme, $storeId
        ));
    }

    public function dataProviderThemeUpdates()
    {
        return [
            [
                [
                    'test_handle_two' => '<some><value>2</value></some>',
                    'test_handle_one' => '<some><value>1</value></some>'
                ],
                'frontend', 'default', 'default', '1'
            ],
            [
                ['test_handle_one' => '<some><value>1</value></some>'],
                'frontend', 'default', 'default', '0'
            ],
            [
                [
                    'test_handle_two' => '<some><value>2</value></some>',
                    'test_handle_three' => "<some><value>4</value></some>\n<some><value>3</value></some>"
                ],
                'frontend', 'default', 'blue', '1'
            ],
            [
                ['test_handle_one' => '<some><value>1</value></some>'],
                'frontend', 'default', 'default', '2'
            ],
            [
                [],
                'frontend', 'my_package', 'default', '1'
            ],
        ];
    }
}
