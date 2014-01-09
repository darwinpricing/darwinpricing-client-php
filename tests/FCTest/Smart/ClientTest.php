<?php

class FCTest_Smart_ClientTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	public function tearDown() {
		unset($_SERVER['REMOTE_ADDR']);
	}

	public function testConstruct() {
		new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com/', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com/api', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com/api/', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com:8080', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com:8080/api', 123456, 'abc');
		new FC_Smart_Client('https://api.darwinpricing.com', 123456, 'abc');
		new FC_Smart_Client('http://user@api.darwinpricing.com', 123456, 'abc');
		new FC_Smart_Client('http://user:pass@api.darwinpricing.com', 123456, 'abc');
	}

	public function testConstruct_BackgroundJob() {
		unset($_SERVER['REMOTE_ADDR']);
		new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc', '127.0.0.1');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_MissingProtocol() {
		new FC_Smart_Client('api.darwinpricing.com', 123456, 'abc');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_Query() {
		new FC_Smart_Client('http://api.darwinpricing.com/?test', 123456, 'abc');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @exceptedExceptionMessage Invalid server URL
	 */
	public function testConstruct_InvalidParameter_Fragment() {
		new FC_Smart_Client('http://api.darwinpricing.com/#test', 123456, 'abc');
	}

	public function testAddPayment() {
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45, 'USD'), null);
		$this->_testAddPayment(new FC_Smart_Client_Price(-123.45, 'USD'), 99);
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45), null);
		$this->_testAddPayment(new FC_Smart_Client_Price(-123), 'v99');
	}

	public function testAddPayment_BackgroundJob() {
		unset($_SERVER['REMOTE_ADDR']);
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45, 'USD'), null, '127.0.0.1');
		$this->_testAddPayment(new FC_Smart_Client_Price(-123.45, 'USD'), 99);
		$this->_testAddPayment(new FC_Smart_Client_Price(123.45), null, '127.0.0.1');
		$this->_testAddPayment(new FC_Smart_Client_Price(-123), 'v99');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_MissingParameter
	 * @expectedExceptionMessage Missing argument `$visitorId`
	 */
	public function testAddPayment_BackgroundJob_MissingParameter() {
		unset($_SERVER['REMOTE_ADDR']);
		$client = new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		$client->addPayment(new FC_Smart_Client_Price(123.45, 'USD'));
	}

	public function testGetDiscountCode_BackgroundJob() {
		unset($_SERVER['REMOTE_ADDR']);
		$discountCodeMock = array('discount-code' => 'WIN13');
		$visitorId = 99;
		$this->_testGetDiscountCode($discountCodeMock, $visitorId);
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_MissingParameter
	 * @expectedExceptionMessage Missing argument `$visitorId`
	 */
	public function testGetDiscountCode_BackgroundJob_MissingParameter() {
		unset($_SERVER['REMOTE_ADDR']);
		$client = new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		$client->getDiscountCode();
	}

	public function testGetDiscountCode_discountCode() {
		$visitorId = 99;
		foreach(array(
			        array('discount-code' => 'WIN13'),
			        array('discount-code' => 'NOËL'),
			        array('discount-code' => ''),
			        array('discount-code' => '123'),
			        array('discount-code' => 123),
			        'Internal Server Error',
			        null,
		        ) as $discountCodeMock) {
			$this->_testGetDiscountCode($discountCodeMock, $visitorId);
		}
	}

	public function testGetDiscountCode_visitorId() {
		$discountCodeMock = array('discount-code' => 'WIN13');
		foreach(array(null, 99, '0123') as $visitorId) {
			$this->_testGetDiscountCode($discountCodeMock, $visitorId);
		}
	}

	public function testGetDynamicPrice_BackgroundJob() {
		unset($_SERVER['REMOTE_ADDR']);
		$serverUrl = 'http://api.darwinpricing.com';
		$serverUrlExpected = 'http://api.darwinpricing.com';
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		$visitorId = 99;
		$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, null, '127.0.0.1');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_MissingParameter
	 * @expectedExceptionMessage Missing argument `$visitorId`
	 */
	public function testGetDynamicPrice_BackgroundJob_MissingParameter() {
		unset($_SERVER['REMOTE_ADDR']);
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$client = new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		$client->getDynamicPrice(FC_Smart_Client_Price::fromArray($referencePrice));
	}

	public function testGetDynamicPrice_serverUrl() {
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		$visitorId = 99;
		foreach(array(
			        'http://api.darwinpricing.com'                      => 'http://api.darwinpricing.com',
			        'http://api.darwinpricing.com/api'                  => 'http://api.darwinpricing.com/api',
			        'http://api.darwinpricing.com/'                     => 'http://api.darwinpricing.com',
			        'http://api.darwinpricing.com/api/'                 => 'http://api.darwinpricing.com/api',
			        'https://user:pass@api.darwinpricing.com:8080/api/' => 'https://user:pass@api.darwinpricing.com:8080/api',
		        ) as $serverUrl => $serverUrlExpected) {
			$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_visitorId() {
		$serverUrl = 'http://api.darwinpricing.com';
		$serverUrlExpected = 'http://api.darwinpricing.com';
		$dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
		$referencePrice = array('value' => 123.45, 'currency' => 'USD');
		$referencePriceExpected = 'USD123.45';
		foreach(array(null, 99, '0123') as $visitorId) {
			$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_referencePrice() {
		$serverUrl = 'http://api.darwinpricing.com';
		$serverUrlExpected = 'http://api.darwinpricing.com';
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
			$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPrice_dynamicPrice() {
		$serverUrl = 'http://api.darwinpricing.com';
		$serverUrlExpected = 'http://api.darwinpricing.com';
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
			$this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
		}
	}

	public function testGetDynamicPriceList() {
		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD')
		);
		$referencePriceExpected = 'USD123.45';
		$dynamicPriceListMock = array(array('value' => 99.95, 'currency' => 'EUR'));
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

		$referencePriceList = array(
			'p1' => array('value' => 123.45, 'currency' => 'USD')
		);
		$referencePriceExpected = 'USD123.45';
		$dynamicPriceListMock = array(array('value' => 99.95, 'currency' => 'EUR'));
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

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
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

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
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

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
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD'),
			array('value' => -123.45, 'currency' => 'USD'),
			array('value' => 100, 'currency' => 'EUR'),
			array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = null;
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

		$referencePriceList = array(
			'p1' => array('value' => 123.45, 'currency' => 'USD'),
			'p2' => array('value' => -123.45, 'currency' => 'USD'),
			'p3' => array('value' => 100, 'currency' => 'EUR'),
			'p4' => array('value' => 10),
		);
		$referencePriceExpected = urlencode('USD123.45,-USD123.45,EUR100,10');
		$dynamicPriceListMock = null;
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);
	}

	public function testGetDynamicPriceList_BackgroundJob() {
		unset($_SERVER['REMOTE_ADDR']);
		$referencePriceList = array(
			array('value' => 123.45, 'currency' => 'USD')
		);
		$referencePriceExpected = 'USD123.45';
		$dynamicPriceListMock = array(array('value' => 99.95, 'currency' => 'EUR'));
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);
		$this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, null, '127.0.0.1');
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_MissingParameter
	 * @expectedExceptionMessage Missing argument `$visitorId`
	 */
	public function testGetDynamicPriceList_BackgroundJob_MissingParameter() {
		unset($_SERVER['REMOTE_ADDR']);
		$referencePriceList = array(
			new FC_Smart_Client_Price(123.45, 'USD'),
			new FC_Smart_Client_Price(100, 'EUR'),
		);
		$client = new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		$client->getDynamicPriceList($referencePriceList);
	}

	/**
	 * @expectedException FC_Smart_Client_Exception_InvalidParameter
	 * @expectedExceptionMessage Invalid reference price list
	 */
	public function testGetDynamicPriceList_InvalidParameter() {
		$client = new FC_Smart_Client('http://api.darwinpricing.com', 123456, 'abc');
		$client->getDynamicPriceList(new FC_Smart_Client_Price(123.45, 'USD'));
	}

	public function testGetDynamicPriceList_InvalidResponse() {
		$referencePriceList = array(new FC_Smart_Client_Price(123.45, 'USD'));
		$referencePriceExpected = 'USD123.45';
		$clientConfig = array('http://api.darwinpricing.com', 123456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(''));
		$urlExpected = 'http://api.darwinpricing.com/get-dynamic-price.php?site-id=123456&hash=abc&visitor-ip=127.0.0.1&reference-price=' . $referencePriceExpected . '&visitor-id=99';
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$dynamicPriceListActual = $client->getDynamicPriceList($referencePriceList, 99);
		$this->assertSame($referencePriceList, $dynamicPriceListActual);
	}

	public function testHttpGet() {
		$this->_testHttpGet('test', 'http://api.darwinpricing.com', 'test');
		$this->_testHttpGet('test', 'http://api.darwinpricing.com', 'testCache');
		$this->_testHttpGet('test', 'http://api.darwinpricing.com', false);

		$this->_testHttpGet(null, 'http://api.darwinpricing.com/?a=2', false);
		$this->_testHttpGet('testNoCache', 'http://api.darwinpricing.com/?a=2', 'testNoCache');
	}

	public function testHttpPost() {
		$this->_testHttpPost('http://api.darwinpricing.com', 'test');
		$this->_testHttpPost('http://api.darwinpricing.com', false);
	}

	protected function _testAddPayment($profit, $visitorId, $visitorIp = null) {
		foreach(array(true, false) as $return) {
			$serverUrl = 'http://api.darwinpricing.com';
			$clientConfig = array($serverUrl, 123456, 'abc', $visitorIp);
			$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpPost'))->setConstructorArgs($clientConfig)->getMock();
			$client->expects($this->any())->method('_httpPost')->will($this->returnValue($return));
			$urlExpected = $serverUrl . '/add-payment.php';
			$parameterListExpected = array(
				'site-id' => 123456,
				'hash'    => 'abc',
			);
			if(isset($_SERVER['REMOTE_ADDR'])) {
				$parameterListExpected['visitor-ip'] = $_SERVER['REMOTE_ADDR'];
			} else {
				$parameterListExpected['visitor-ip'] = $visitorIp;
			}
			$parameterListExpected['profit'] = (string) $profit;
			$parameterListExpected['visitor-id'] = $visitorId;
			$client->expects($this->once())->method('_httpPost')->with($urlExpected, $parameterListExpected);

			$this->assertSame($return, $client->addPayment($profit, $visitorId));
		}
	}

	protected function _testGetDiscountCode($discountCodeMock, $visitorId, $visitorIp = null) {
		$serverUrl = 'http://api.darwinpricing.com';
		$clientConfig = array($serverUrl, 123456, 'abc', $visitorIp);
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(null !== $discountCodeMock ? json_encode($discountCodeMock) : null));
		$urlExpected = $serverUrl . '/get-discount-code.php?site-id=123456&hash=abc';
		if(null !== $visitorIp) {
			$urlExpected .= '&visitor-ip=' . $visitorIp;
		} elseif(isset($_SERVER['REMOTE_ADDR'])) {
			$urlExpected .= '&visitor-ip=' . $_SERVER['REMOTE_ADDR'];
		}
		if(null !== $visitorId) {
			$urlExpected .= '&visitor-id=' . $visitorId;
		}
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$discountCodeActual = $client->getDiscountCode($visitorId);
		$this->assertTrue(is_string($discountCodeActual));
		$discountCodeExpected = is_array($discountCodeMock) ? (string) $discountCodeMock['discount-code'] : '';
		$this->assertSame($discountCodeExpected, $discountCodeActual);
	}

	protected function _testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId, $visitorIp = null) {
		$clientConfig = array($serverUrl, 123456, 'abc', $visitorIp);
		$serverUrlExpected = isset($serverUrlExpected) ? $serverUrlExpected : $serverUrl;
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(null !== $dynamicPriceMock ? json_encode($dynamicPriceMock) : null));
		$urlExpected = $serverUrlExpected . '/get-dynamic-price.php?site-id=123456&hash=abc';
		if(null !== $visitorIp) {
			$urlExpected .= '&visitor-ip=' . $visitorIp;
		} elseif(isset($_SERVER['REMOTE_ADDR'])) {
			$urlExpected .= '&visitor-ip=' . $_SERVER['REMOTE_ADDR'];
		}
		$urlExpected .= '&reference-price=' . $referencePriceExpected;
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

	protected function _testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, $visitorId, $visitorIp = null) {
		$clientConfig = array('http://api.darwinpricing.com', 123456, 'abc', $visitorIp);
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_httpGet'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_httpGet')->will($this->returnValue(null !== $dynamicPriceListMock ? json_encode($dynamicPriceListMock) : null));
		$urlExpected = 'http://api.darwinpricing.com/get-dynamic-price.php?site-id=123456&hash=abc';
		if(null !== $visitorIp) {
			$urlExpected .= '&visitor-ip=' . $visitorIp;
		} elseif(isset($_SERVER['REMOTE_ADDR'])) {
			$urlExpected .= '&visitor-ip=' . $_SERVER['REMOTE_ADDR'];
		}
		$urlExpected .= '&reference-price=' . $referencePriceExpected;
		if(null !== $visitorId) {
			$urlExpected .= '&visitor-id=' . $visitorId;
		}
		$client->expects($this->once())->method('_httpGet')->with($urlExpected);

		$dynamicPriceListActual = $client->getDynamicPriceList(array_map(array(
		                                                                      'FC_Smart_Client_Price',
		                                                                      'fromArray'
		                                                                 ), $referencePriceList), $visitorId);
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
		$clientConfig = array('http://api.darwinpricing.com', 123456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_curlExec'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_curlExec')->will($this->returnValue($curlResultMock));
		$class = new ReflectionClass($client);
		$_httpGet = $class->getMethod('_httpGet');
		$_httpGet->setAccessible(true);
		$this->assertSame($expected, $_httpGet->invokeArgs($client, array($url)));
	}

	protected function _testHttpPost($url, $curlResultMock) {
		$clientConfig = array('http://api.darwinpricing.com', 123456, 'abc');
		$client = $this->getMockBuilder('FC_Smart_Client')->setMethods(array('_curlExec'))->setConstructorArgs($clientConfig)->getMock();
		$client->expects($this->any())->method('_curlExec')->will($this->returnValue($curlResultMock));
		$class = new ReflectionClass($client);
		$_httpPost = $class->getMethod('_httpPost');
		$_httpPost->setAccessible(true);
		$this->assertSame($curlResultMock, $_httpPost->invokeArgs($client, array($url, array())));
	}
}
