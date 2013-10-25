<?php

class FCTest_Smart_ClientTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	public function testConstruct() {
		new FC_Smart_Client('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		new FC_Smart_Client('http://api.smart-prices-localizer.com/', 123, 456, 'abc');
		new FC_Smart_Client('http://api.smart-prices-localizer.com/api', 123, 456, 'abc');
		new FC_Smart_Client('http://api.smart-prices-localizer.com/api/', 123, 456, 'abc');
		new FC_Smart_Client('http://api.smart-prices-localizer.com:8080', 123, 456, 'abc');
		new FC_Smart_Client('http://api.smart-prices-localizer.com:8080/api', 123, 456, 'abc');
		new FC_Smart_Client('https://api.smart-prices-localizer.com', 123, 456, 'abc');
		new FC_Smart_Client('http://user@api.smart-prices-localizer.com', 123, 456, 'abc');
		new FC_Smart_Client('http://user:pass@api.smart-prices-localizer.com', 123, 456, 'abc');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_MissingProtocol() {
		new FC_Smart_Client('api.smart-prices-localizer.com', 123, 456, 'abc');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_Query() {
		new FC_Smart_Client('http://api.smart-prices-localizer.com/?test', 123, 456, 'abc');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_Fragment() {
		new FC_Smart_Client('http://api.smart-prices-localizer.com/#test', 123, 456, 'abc');
	}

	public function testAddPayment() {
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45, 'USD'), null);
		$this->_testAddPayment(new FC_Smart_Client_Price(-123.45, 'USD'), 99);
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45), null);
		$this->_testAddPayment(new FC_Smart_Client_Price(-123), 'v99');
	}

	public function testGetDynamicPrice_serverUrl() {
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		$visitorId = 99;
		foreach(array(
			        'http://api.smart-prices-localizer.com'                      => 'http://api.smart-prices-localizer.com',
			        'http://api.smart-prices-localizer.com/api'                  => 'http://api.smart-prices-localizer.com/api',
			        'http://api.smart-prices-localizer.com/'                     => 'http://api.smart-prices-localizer.com',
			        'http://api.smart-prices-localizer.com/api/'                 => 'http://api.smart-prices-localizer.com/api',
			        'https://user:pass@api.smart-prices-localizer.com:8080/api/' => 'https://user:pass@api.smart-prices-localizer.com:8080/api',
		        ) as $serverUrl => $serverUrlExpected) {
			$this->_testDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_visitorId() {
		$serverUrl = 'http://api.smart-prices-localizer.com';
		$serverUrlExpected = 'http://api.smart-prices-localizer.com';
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		foreach(array(null, 99, '0123') as $visitorId) {
			$this->_testDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_referencePrice() {
		$serverUrl = 'http://api.smart-prices-localizer.com';
		$serverUrlExpected = 'http://api.smart-prices-localizer.com';
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$visitorId = 99;
		foreach(array(
			        array('value' => 123.45, 'currency' => 'USD', 'expected' => 'USD123.45'),
			        array('value' => -123.45, 'currency' => 'USD', 'expected' => '-USD123.45'),
			        array('value' => 123, 'currency' => 'USD', 'expected' => 'USD123'),
			        array('value' => -123, 'currency' => 'USD', 'expected' => '-USD123'),
			        array('value' => 123.45, 'expected' => '123.45'),
			        array('value' => -123.45, 'expected' => '-123.45'),
			        array('value' => 123, 'expected' => '123'),
			        array('value' => -123, 'expected' => '-123'),
		        ) as $referencePrice) {
			$referencePriceExpected = $referencePrice['expected'];
			$this->_testDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_dynamicPrice() {
		$serverUrl = 'http://api.smart-prices-localizer.com';
		$serverUrlExpected = 'http://api.smart-prices-localizer.com';
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		$visitorId = 99;
		foreach(array(
			        array('value' => 99.95, 'currency' => 'EUR'),
			        array('value' => -99.95, 'currency' => 'EUR'),
			        array('value' => 100., 'currency' => 'EUR'),
			        array('value' => -100., 'currency' => 'EUR'),
			        array('value' => 99.95),
			        array('value' => -99.95),
			        array('value' => 100.),
			        array('value' => -100.),
			        null,
		        ) as $dynamicPriceMock) {
			$this->_testDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPriceList() {
		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD')
		);
		$referencePriceExpected = 'USD123.45';
		$dynamicPriceListMock = array(array('value' => 99.95, 'currency' => 'EUR'));
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			'p1' => array('value' => 123.45, 'currency' => 'USD')
		);
		$referencePriceExpected = 'USD123.45';
		$dynamicPriceListMock = array(array('value' => 99.95, 'currency' => 'EUR'));
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD'),
			array('value' => -123.45, 'currency' => 'USD'),
			array('value' => 100, 'currency' => 'EUR'),
			array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = array(
			array('value' => 99.95, 'currency' => 'EUR'),
			array('value' => -99.95, 'currency' => 'EUR'),
			array('value' => 100., 'currency' => 'EUR'),
			array('value' => 8.),
		);
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			'p1' => array('value' => 123.45, 'currency' => 'USD'),
			'p2' => array('value' => -123.45, 'currency' => 'USD'),
			'p3' => array('value' => 100, 'currency' => 'EUR'),
			'p4' => array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = array(
			array('value' => 99.95, 'currency' => 'EUR'),
			array('value' => -99.95, 'currency' => 'EUR'),
			array('value' => 100., 'currency' => 'EUR'),
			array('value' => 8.),
		);
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			4 => array('value' => 123.45, 'currency' => 'USD'),
			2 => array('value' => -123.45, 'currency' => 'USD'),
			1 => array('value' => 100, 'currency' => 'EUR'),
			3 => array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = array(
			array('value' => 99.95, 'currency' => 'EUR'),
			array('value' => -99.95, 'currency' => 'EUR'),
			array('value' => 100., 'currency' => 'EUR'),
			array('value' => 8.),
		);
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD'),
			array('value' => -123.45, 'currency' => 'USD'),
			array('value' => 100, 'currency' => 'EUR'),
			array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = null;
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);

		$referencePriceList = array(
			'p1' => array('value' => 123.45, 'currency' => 'USD'),
			'p2' => array('value' => -123.45, 'currency' => 'USD'),
			'p3' => array('value' => 100, 'currency' => 'EUR'),
			'p4' => array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = null;
		$this->_testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock);
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @expectedExceptionMessage Invalid reference price list
	 */
	public function testDynamicPriceList_InvalidParameter() {
		$client = new FC_Smart_Client('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		$client->getDynamicPriceList(new FC_Smart_Client_Price(123.45, 'USD'));
	}

	public function testDynamicPriceList_InvalidResponse() {
		$referencePriceList = array(new FC_Smart_Client_Price(123.45, 'USD'));
		$referencePriceExpected = 'USD123.45';
		$clientConfig = array('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(''));
		$urlExpected = 'http://api.smart-prices-localizer.com/get-dynamic-price?customer-id=123&site-id=456&hash=abc&visitor-ip=127.0.0.1&reference-price=' . $referencePriceExpected . '&visitor-id=99';
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$dynamicPriceListActual = $client->getDynamicPriceList($referencePriceList, 99);
		$this->assertSame($referencePriceList, $dynamicPriceListActual);
	}

	public function testHttpGet() {
		$this->_testHttpGet('test', 'http://api.smart-prices-localizer.com', 'test');
		$this->_testHttpGet('test', 'http://api.smart-prices-localizer.com', 'testCache');
		$this->_testHttpGet('test', 'http://api.smart-prices-localizer.com', false);

		$this->_testHttpGet(null, 'http://api.smart-prices-localizer.com/?a=2', false);
		$this->_testHttpGet('testNoCache', 'http://api.smart-prices-localizer.com/?a=2', 'testNoCache');
	}

	public function testHttpPost() {
		$this->_testHttpPost('http://api.smart-prices-localizer.com', 'test');
		$this->_testHttpPost('http://api.smart-prices-localizer.com', false);
	}

	protected function _testAddPayment($profit, $visitorId) {
		foreach(array(true, false) as $return) {
			$serverUrl = 'http://api.smart-prices-localizer.com';
			$clientConfig = array($serverUrl, 123, 456, 'abc');
			$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpPost'))->setConstructorArgs($clientConfig)->getMock();
			$client->expects($this->any())->method('_httpPost')->will($this->returnValue($return));
			$urlExpected = $serverUrl . '/add-payment?customer-id=123&site-id=456&hash=abc&visitor-ip=127.0.0.1&profit=' . $profit;
			if(null !== $visitorId) {
				$urlExpected .= '&visitor-id=' . $visitorId;
			}
			$client->expects($this->once())->method('_httpPost')->with($urlExpected);

			$this->assertSame($return, $client->recordPurchase($profit, $visitorId));
		}
	}

	protected function _testDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId) {
		$clientConfig = array($serverUrl, 123, 456, 'abc');
		$serverUrlExpected = isset($serverUrlExpected) ? $serverUrlExpected : $serverUrl;
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(null !== $dynamicPriceMock ? json_encode($dynamicPriceMock) : null));
		$urlExpected = $serverUrlExpected . '/get-dynamic-price?customer-id=123&site-id=456&hash=abc&visitor-ip=127.0.0.1&reference-price=' . $referencePriceExpected;
		if(null !== $visitorId) {
			$urlExpected .= '&visitor-id=' . $visitorId;
		}
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$dynamicPriceActual = $client->getDynamicPrice(FC_Smart_Client_Price::fromArray($referencePrice), $visitorId);
		$this->assertInstanceOf('FC_Smart_Client_Price', $dynamicPriceActual);
		$dynamicPriceExpected = null !== $dynamicPriceMock ? $dynamicPriceMock : $referencePrice;
		$this->assertSame((float) $dynamicPriceExpected['value'], $dynamicPriceActual->getValue());
		$this->assertSame(isset($dynamicPriceExpected['currency']) ? $dynamicPriceExpected['currency'] : null, $dynamicPriceActual->getCurrency());
	}

	protected function _testDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock) {
		$clientConfig = array('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(null !== $dynamicPriceListMock ? json_encode($dynamicPriceListMock) : null));
		$urlExpected = 'http://api.smart-prices-localizer.com/get-dynamic-price?customer-id=123&site-id=456&hash=abc&visitor-ip=127.0.0.1&reference-price=' . $referencePriceExpected . '&visitor-id=99';
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$dynamicPriceListActual = $client->getDynamicPriceList(array_map(array(
		                                                                      'FC_Smart_Client_Price',
		                                                                      'fromArray'
		                                                                 ), $referencePriceList), 99);
		$this->assertTrue(is_array($dynamicPriceListActual));
		foreach($dynamicPriceListActual as $dynamicPriceActual) {
			$this->assertInstanceOf('FC_Smart_Client_Price', $dynamicPriceActual);
		}
		if(null === $dynamicPriceListMock) {
			$dynamicPriceListExpected = $referencePriceList;
		} else {
			$dynamicPriceListExpected = array();
			foreach(array_keys($referencePriceList) as $i => $key) {
				$dynamicPriceListExpected[$key] = $dynamicPriceListMock[$i];
			}
		}
		$this->assertSame(count($dynamicPriceListExpected), count($dynamicPriceListActual));
		foreach($dynamicPriceListExpected as $key => $dynamicPriceExpected) {
			$this->assertTrue(isset($dynamicPriceListActual[$key]));
			$this->assertSame((float) $dynamicPriceExpected['value'], $dynamicPriceListActual[$key]->getValue());
			$this->assertSame(isset($dynamicPriceExpected['currency']) ? $dynamicPriceExpected['currency'] : null, $dynamicPriceListActual[$key]->getCurrency());
		}
	}

	protected function _testHttpGet($expected, $url, $curlResultMock) {
		$clientConfig = array('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_curlExec'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_curlExec')->will($this->returnValue($curlResultMock));
		$class = new ReflectionClass($client);
		$_httpGet = $class->getMethod('_httpGet');
		$_httpGet->setAccessible(true);
		$this->assertSame($expected, $_httpGet->invokeArgs($client, array($url)));
	}

	protected function _testHttpPost($url, $curlResultMock) {
		$clientConfig = array('http://api.smart-prices-localizer.com', 123, 456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_curlExec'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_curlExec')->will($this->returnValue($curlResultMock));
		$class = new ReflectionClass($client);
		$_httpPost = $class->getMethod('_httpPost');
		$_httpPost->setAccessible(true);
		$this->assertSame($curlResultMock, $_httpPost->invokeArgs($client, array($url)));
	}
}
