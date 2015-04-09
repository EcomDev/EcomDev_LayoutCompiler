<?php

/**
 * Trait for cache aware interface implementation
 *
 */
class EcomDev_LayoutCompiler_CacheAwareTraitTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var EcomDev_LayoutCompiler_CacheAwareTrait|EcomDev_LayoutCompiler_Contract_CacheAwareInterface
     */
    protected $trait;
    
    protected function setUp()
    {
        $this->trait = $this->getMockForTrait('EcomDev_LayoutCompiler_CacheAwareTrait');
    }
    
    public function testItIsPossibleToSetCacheInstance()
    {
        $cache = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CacheInterface');
        $this->trait->setCache($cache);
        $this->assertAttributeSame($cache, 'cache', $this->trait);
    }
    
    public function testItIsPossibleToRetrieveCacheInstance()
    {
        $cache = $this->getMockForAbstractClass('EcomDev_LayoutCompiler_Contract_CacheInterface');
        $this->trait->setCache($cache);
        $this->assertSame($cache, $this->trait->getCache());
    }
}
