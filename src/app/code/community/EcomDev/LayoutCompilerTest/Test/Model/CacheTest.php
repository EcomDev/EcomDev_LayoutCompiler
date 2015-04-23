<?php

class EcomDev_LayoutCompilerTest_Test_Model_CacheTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_Model_Cache
     */
    private $cache;

    protected function setUp()
    {
        $this->cache = new EcomDev_LayoutCompiler_Model_Cache();
    }

    public function testItUsesApplicationAsDefaultOne()
    {
        $this->assertSame(Mage::app(), $this->cache->getApp());
    }

    public function testItIsPossibleToSetCustomApp()
    {
        $app = $this->getMock('Mage_Core_Model_App');
        $this->assertSame($this->cache, $this->cache->setApp($app));
        $this->assertSame($app, $this->cache->getApp());
    }

    public function testItRetrievesDataFromCacheInApplication()
    {
        $app = $this->getMock('Mage_Core_Model_App');
        $app->expects($this->once())
            ->method('loadCache')
            ->with('test_key_for_item')
            ->willReturn('{"name":"value"}');

        $this->cache->setApp($app);
        $this->assertSame(
            ['name' => 'value'],
            $this->cache->load('test_key_for_item')
        );
    }

    public function testItReturnsFalseIfCacheInAppReturnsFalse()
    {
        $app = $this->getMock('Mage_Core_Model_App');
        $app->expects($this->once())
            ->method('loadCache')
            ->with('test_key_for_item')
            ->willReturn(false);

        $this->cache->setApp($app);
        $this->assertFalse(
            $this->cache->load('test_key_for_item')
        );
    }

    public function testItSavesCachedDataIntoAppObject()
    {
        $app = $this->getMock('Mage_Core_Model_App');
        $app->expects($this->once())
            ->method('saveCache')
            ->with(
                '{"key":"value"}',
                'test_key_for_item',
                array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG),
                3600
            )
            ->willReturnSelf();

        $this->cache->setApp($app);
        $this->assertSame(
            $this->cache,
            $this->cache->save('test_key_for_item', array('key' => 'value'), 3600)
        );
    }
}
