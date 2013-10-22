<?php

class FC_SmartPricesLocalizer_Client_CacheTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		FC_SmartPricesLocalizer_Client_Cache::flush();
	}

	public function testGet() {
		$cacheKey = __METHOD__;
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
	}

	public function testSet() {
		$cacheKey = __METHOD__;
		$cacheKey2 = $cacheKey . '2';
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey, 'test');
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey2, 'test2');
		$this->assertSame('test', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame('test2', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
	}

	public function testDelete() {
		$cacheKey = __METHOD__;
		$cacheKey2 = $cacheKey . '2';
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey, 'test');
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey2, 'test2');
		$this->assertSame('test', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame('test2', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
		FC_SmartPricesLocalizer_Client_Cache::delete($cacheKey);
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame('test2', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
	}

	public function testFlush() {
		$cacheKey = __METHOD__;
		$cacheKey2 = $cacheKey . '2';
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey, 'test');
		FC_SmartPricesLocalizer_Client_Cache::set($cacheKey2, 'test2');
		$this->assertSame('test', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame('test2', FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
		FC_SmartPricesLocalizer_Client_Cache::flush();
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey));
		$this->assertSame(false, FC_SmartPricesLocalizer_Client_Cache::get($cacheKey2));
	}
}
