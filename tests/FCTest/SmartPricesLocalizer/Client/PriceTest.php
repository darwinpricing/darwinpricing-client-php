<?php

class FC_SmartPricesLocalizer_Client_PriceTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		new FC_SmartPricesLocalizer_Client_Price(123);
		new FC_SmartPricesLocalizer_Client_Price(-123);
		new FC_SmartPricesLocalizer_Client_Price(123.45);
		new FC_SmartPricesLocalizer_Client_Price(-123.45);
		new FC_SmartPricesLocalizer_Client_Price(123.45, 'USD');
		new FC_SmartPricesLocalizer_Client_Price(-123.45, 'USD');
	}

	public function testToString() {
		$price = new FC_SmartPricesLocalizer_Client_Price(123);
		$this->assertSame('123', (string) $price);
		$price = new FC_SmartPricesLocalizer_Client_Price(-123);
		$this->assertSame('-123', (string) $price);
		$price = new FC_SmartPricesLocalizer_Client_Price(123.45);
		$this->assertSame('123.45', (string) $price);
		$price = new FC_SmartPricesLocalizer_Client_Price(-123.45);
		$this->assertSame('-123.45', (string) $price);
		$price = new FC_SmartPricesLocalizer_Client_Price(123.45, 'USD');
		$this->assertSame('USD123.45', (string) $price);
		$price = new FC_SmartPricesLocalizer_Client_Price(-123.45, 'USD');
		$this->assertSame('-USD123.45', (string) $price);
	}

	public function testGetCurrency() {
		$price = new FC_SmartPricesLocalizer_Client_Price(123.45);
		$this->assertSame(null, $price->getCurrency());
		$price = new FC_SmartPricesLocalizer_Client_Price(123.45, 'USD');
		$this->assertSame('USD', $price->getCurrency());
	}

	public function testGetValue() {
		$price = new FC_SmartPricesLocalizer_Client_Price(123.45);
		$this->assertSame(123.45, $price->getValue());
		$price = new FC_SmartPricesLocalizer_Client_Price(123);
		$this->assertSame(123., $price->getValue());
		$price = new FC_SmartPricesLocalizer_Client_Price(-123.45);
		$this->assertSame(-123.45, $price->getValue());
	}

	public function testFromArray() {
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => 123));
		$this->assertSame(123., $price->getValue());
		$this->assertSame(null, $price->getCurrency());
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => -123));
		$this->assertSame(-123., $price->getValue());
		$this->assertSame(null, $price->getCurrency());
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => 123.45));
		$this->assertSame(123.45, $price->getValue());
		$this->assertSame(null, $price->getCurrency());
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => -123.45));
		$this->assertSame(-123.45, $price->getValue());
		$this->assertSame(null, $price->getCurrency());
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => 123.45, 'currency' => 'USD'));
		$this->assertSame(123.45, $price->getValue());
		$this->assertSame('USD', $price->getCurrency());
		$price = FC_SmartPricesLocalizer_Client_Price::fromArray(array('value' => -123.45, 'currency' => 'USD'));
		$this->assertSame(-123.45, $price->getValue());
		$this->assertSame('USD', $price->getCurrency());
	}

	/**
	 * @expectedException FC_SmartPricesLocalizer_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid price data array
	 */
	public function testFromArray_InvalidParameter_NotAnArray() {
		FC_SmartPricesLocalizer_Client_Price::fromArray(123);
	}

	/**
	 * @expectedException FC_SmartPricesLocalizer_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid price data array
	 */
	public function testFromArray_InvalidParameter_MissingValue() {
		FC_SmartPricesLocalizer_Client_Price::fromArray(array('currency' => 'USD'));
	}
}
