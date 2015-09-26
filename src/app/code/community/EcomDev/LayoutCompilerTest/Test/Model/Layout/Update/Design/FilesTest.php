<?php
use \org\bovigo\vfs\vfsStream as Stream;
use \org\bovigo\vfs\vfsStreamDirectory as StreamDirectory;

/**
 * Tests a design files retriever
 *
 * @loadSharedFixture config
 */
class EcomDev_LayoutCompilerTest_Test_Model_Layout_Update_Design_FilesTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Design files model
     *
     * @var EcomDev_LayoutCompiler_Model_Layout_Update_Design_Files
     */
    private $designFiles;

    /**
     * @var StreamDirectory
     */
    private $vfsRoot;

    protected function setUp()
    {
        $this->designFiles = new EcomDev_LayoutCompiler_Model_Layout_Update_Design_Files();
        $this->vfsRoot = Stream::setup();
        $this->app()->disableEvents();
    }

    protected function tearDown()
    {
        $this->app()->enableEvents();
    }

    private function validateDesignConfig()
    {
        if (method_exists('Mage', 'getEdition')) {
            $editionVersion = [
                Mage::EDITION_ENTERPRISE => '1.14.0',
                Mage::EDITION_PROFESSIONAL => '1.14.0',
                Mage::EDITION_COMMUNITY => '1.9.0'
            ];

            if (!version_compare(Mage::getVersion(), $editionVersion[Mage::getEdition()], '>=')) {
                $this->markTestSkipped('Does not supported in this Magento version');
            }
        }

        return false;
    }

    public function testItTakesDefaultMagentoConfigModel()
    {
        $this->assertSame(Mage::getConfig(), $this->designFiles->getConfig());
    }

    /**
     * @singleton core/design_config
     */
    public function testItTakesDefaultMagentoDesignConfigModel()
    {
        $this->validateDesignConfig();
        $this->assertSame(
            Mage::getSingleton('core/design_config'),
            $this->designFiles->getDesignConfig()
        );
    }

    public function testItUsesOverriddenConfigModelWhenItIsSet()
    {
        $configModel = $this->getMock('Mage_Core_Model_Config');
        $this->assertSame(
            $this->designFiles,
            $this->designFiles->setConfig($configModel)
        );

        $this->assertSame(
            $configModel,
            $this->designFiles->getConfig()
        );
    }

    public function testItUsesOverriddenDesignConfigModelWhenItIsSet()
    {
        $this->validateDesignConfig();
        $configModel = $this->getMockBuilder('Mage_Core_Model_Design_Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $this->designFiles,
            $this->designFiles->setDesignConfig($configModel)
        );

        $this->assertSame(
            $configModel,
            $this->designFiles->getDesignConfig()
        );
    }

    public function testItReturnsListOfFilesIncludingThemeUpdates()
    {
        $this->validateDesignConfig();

        $designConfig = $this->getMockBuilder('Mage_Core_Model_Design_Config')
            ->disableOriginalConstructor()
            ->getMock();

        $config = $this->getMock('Mage_Core_Model_Config');

        $this->designFiles->setDesignConfig($designConfig);
        $this->designFiles->setConfig($config);

        $config->expects($this->once())
            ->method('getNode')
            ->with('frontend/layout/updates')
            ->willReturn($this->createLayoutUpdateNodes([
                'file1.xml',
                'file2.xml'
            ]));

        $designConfig->expects($this->once())
            ->method('getNode')
            ->with('frontend/ecomdev/compiler/layout/updates')
            ->willReturn($this->createLayoutUpdateNodes([
                'file4.xml',
                'file5.xml'
            ]));

        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $designParams = [
            '_area'    => 'frontend',
            '_package' => 'ecomdev',
            '_theme'   => 'compiler'
        ];

        $designPackage->expects($this->exactly(5))
            ->method('getLayoutFilename')
            ->withConsecutive(
                ['file1.xml', $designParams],
                ['file2.xml', $designParams],
                ['file4.xml', $designParams],
                ['file5.xml', $designParams],
                ['local.xml', $designParams]
            )
            ->willReturnOnConsecutiveCalls(
                's/file1.xml', 's/file2.xml', 's/file4.xml', 's/file5.xml', 's/local.xml'
            )
        ;

        $this->assertSame(
            ['s/file1.xml', 's/file2.xml', 's/file4.xml', 's/file5.xml', 's/local.xml'],
            $this->designFiles->getDesignLayoutFiles(
                $designPackage, 'frontend', 'ecomdev', 'compiler', '0'
            )
        );
    }

    public function testItReturnsListOfFilesIfThemeDesignIsNotAvailable()
    {
        $config = $this->getMock('Mage_Core_Model_Config');

        $this->designFiles->setDesignConfig(false);
        $this->designFiles->setConfig($config);

        $config->expects($this->once())
            ->method('getNode')
            ->with('frontend/layout/updates')
            ->willReturn($this->createLayoutUpdateNodes([
                'file1.xml',
                'file2.xml' => 'Disabled_Module_In_Default_Store'
            ]));

        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $designParams = [
            '_area'    => 'frontend',
            '_package' => 'ecomdev',
            '_theme'   => 'compiler'
        ];

        $designPackage->expects($this->exactly(3))
            ->method('getLayoutFilename')
            ->withConsecutive(
                ['file1.xml', $designParams],
                ['file2.xml', $designParams],
                ['local.xml', $designParams]
            )
            ->willReturnOnConsecutiveCalls(
                's/file1.xml', 's/file2.xml', 's/local.xml'
            )
        ;

        $this->assertSame(
            ['s/file1.xml', 's/file2.xml', 's/local.xml'],
            $this->designFiles->getDesignLayoutFiles(
                $designPackage, 'frontend', 'ecomdev', 'compiler', '0'
            )
        );

        $this->assertEventDispatched('core_layout_update_updates_get_after');
    }

    public function testItDoesNotReturnDisabledModuleLayout()
    {
        $config = $this->getMock('Mage_Core_Model_Config');

        $this->designFiles->setDesignConfig(false);
        $this->designFiles->setConfig($config);

        $config->expects($this->once())
            ->method('getNode')
            ->with('frontend/layout/updates')
            ->willReturn($this->createLayoutUpdateNodes([
                'file1.xml',
                'file2.xml' => 'Disabled_Module_In_Default_Store'
            ]));

        $designPackage = $this->getMock('Mage_Core_Model_Design_Package');
        $designParams = [
            '_area'    => 'frontend',
            '_package' => 'ecomdev',
            '_theme'   => 'compiler'
        ];

        $designPackage->expects($this->exactly(2))
            ->method('getLayoutFilename')
            ->withConsecutive(
                ['file1.xml', $designParams],
                ['local.xml', $designParams]
            )
            ->willReturnOnConsecutiveCalls(
                's/file1.xml', 's/local.xml'
            )
        ;

        $this->assertSame(
            ['s/file1.xml', 's/local.xml'],
            $this->designFiles->getDesignLayoutFiles(
                $designPackage, 'frontend', 'ecomdev', 'compiler', '1'
            )
        );
    }


    private function createXmlFromFileName($fileName, $module)
    {
        $nodeId = 'node' . uniqid();
        $xml = '<' . $nodeId . ($module ? ' module="' . $module . '"' : '') . '>';
        $xml .= '<file><![CDATA[' . htmlentities($fileName) . ']]></file>';
        $xml .= '</' . $nodeId . '>';
        return $xml;
    }

    /**
     * Creates xml nodes from file list
     *
     * @param array $files
     * @return Varien_Simplexml_Element
     */
    private function createLayoutUpdateNodes($files)
    {
        $xml = '<updates>';
        foreach ($files as $file => $module) {
            if (is_int($file)) {
                $file = $module;
                $module = false;
            }

            $xml .= $this->createXmlFromFileName($file, $module);
        }
        $xml .= '</updates>';

        return new Varien_Simplexml_Element($xml);
    }
}
