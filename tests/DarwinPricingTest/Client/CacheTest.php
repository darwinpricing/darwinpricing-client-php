<?php

class DarwinPricingTest_Client_CacheTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->_getCache()->flush();
    }

    public function testGet() {
        $cacheKey = __METHOD__;
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
    }

    public function testSet() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
        $this->assertSame(false, $this->_getCache()->get($cacheKey2));
        $this->_getCache()->set($cacheKey, 'test');
        $this->_getCache()->set($cacheKey2, 'test2');
        $this->assertSame('test', $this->_getCache()->get($cacheKey));
        $this->assertSame('test2', $this->_getCache()->get($cacheKey2));
    }

    public function testDelete() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
        $this->assertSame(false, $this->_getCache()->get($cacheKey2));
        $this->_getCache()->set($cacheKey, 'test');
        $this->_getCache()->set($cacheKey2, 'test2');
        $this->assertSame('test', $this->_getCache()->get($cacheKey));
        $this->assertSame('test2', $this->_getCache()->get($cacheKey2));
        $this->_getCache()->delete($cacheKey);
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
        $this->assertSame('test2', $this->_getCache()->get($cacheKey2));
    }

    public function testFlush() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
        $this->assertSame(false, $this->_getCache()->get($cacheKey2));
        $this->_getCache()->set($cacheKey, 'test');
        $this->_getCache()->set($cacheKey2, 'test2');
        $this->assertSame('test', $this->_getCache()->get($cacheKey));
        $this->assertSame('test2', $this->_getCache()->get($cacheKey2));
        $this->_getCache()->flush();
        $this->assertSame(false, $this->_getCache()->get($cacheKey));
        $this->assertSame(false, $this->_getCache()->get($cacheKey2));
    }

    protected function _getCache() {
        return new DarwinPricing_Client_Cache();
    }

}
