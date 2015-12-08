<?php

class DarwinPricingTest_Client_CacheTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        DarwinPricing_Client_Cache::flush();
    }

    public function testGet() {
        $cacheKey = __METHOD__;
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
    }

    public function testSet() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey2));
        DarwinPricing_Client_Cache::set($cacheKey, 'test');
        DarwinPricing_Client_Cache::set($cacheKey2, 'test2');
        $this->assertSame('test', DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame('test2', DarwinPricing_Client_Cache::get($cacheKey2));
    }

    public function testDelete() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey2));
        DarwinPricing_Client_Cache::set($cacheKey, 'test');
        DarwinPricing_Client_Cache::set($cacheKey2, 'test2');
        $this->assertSame('test', DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame('test2', DarwinPricing_Client_Cache::get($cacheKey2));
        DarwinPricing_Client_Cache::delete($cacheKey);
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame('test2', DarwinPricing_Client_Cache::get($cacheKey2));
    }

    public function testFlush() {
        $cacheKey = __METHOD__;
        $cacheKey2 = $cacheKey . '2';
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey2));
        DarwinPricing_Client_Cache::set($cacheKey, 'test');
        DarwinPricing_Client_Cache::set($cacheKey2, 'test2');
        $this->assertSame('test', DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame('test2', DarwinPricing_Client_Cache::get($cacheKey2));
        DarwinPricing_Client_Cache::flush();
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey));
        $this->assertSame(false, DarwinPricing_Client_Cache::get($cacheKey2));
    }

}
