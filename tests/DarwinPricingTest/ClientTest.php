<?php

class DarwinPricingTest_ClientTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function tearDown() {
        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testConstruct() {
        new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com/', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com/api', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com/api/', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com:8080', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com:8080/api', 123456, 'abc');
        new DarwinPricing_Client('https://api.darwinpricing.com', 123456, 'abc');
        new DarwinPricing_Client('http://user@api.darwinpricing.com', 123456, 'abc');
        new DarwinPricing_Client('http://user:pass@api.darwinpricing.com', 123456, 'abc');
    }

    public function testConstruct_BackgroundJob() {
        unset($_SERVER['REMOTE_ADDR']);
        new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc', new DarwinPricing_Client_Visitor('127.0.0.1'));
    }

    /**
     * @expectedException DarwinPricing_Client_Exception_InvalidParameter
     * @exceptedExceptionMessage Invalid server URL
     */
    public function testConstruct_InvalidParameter_MissingProtocol() {
        new DarwinPricing_Client('api.darwinpricing.com', 123456, 'abc');
    }

    /**
     * @expectedException DarwinPricing_Client_Exception_InvalidParameter
     * @exceptedExceptionMessage Invalid server URL
     */
    public function testConstruct_InvalidParameter_Query() {
        new DarwinPricing_Client('http://api.darwinpricing.com/?test', 123456, 'abc');
    }

    /**
     * @expectedException DarwinPricing_Client_Exception_InvalidParameter
     * @exceptedExceptionMessage Invalid server URL
     */
    public function testConstruct_InvalidParameter_Fragment() {
        new DarwinPricing_Client('http://api.darwinpricing.com/#test', 123456, 'abc');
    }

    public function testAddPayment() {
        $this->_testAddPayment(new DarwinPricing_Client_Price(123.45, 'USD'), null);
        $this->_testAddPayment(new DarwinPricing_Client_Price(-123.45, 'USD'), 99);
        $this->_testAddPayment(new DarwinPricing_Client_Price(123.45), null);
        $this->_testAddPayment(new DarwinPricing_Client_Price(-123), 'v99');
    }

    public function testAddPayment_BackgroundJob() {
        unset($_SERVER['REMOTE_ADDR']);
        $this->_testAddPayment(new DarwinPricing_Client_Price(123.45, 'USD'), null, '127.0.0.1');
        $this->_testAddPayment(new DarwinPricing_Client_Price(-123.45, 'USD'), 99);
        $this->_testAddPayment(new DarwinPricing_Client_Price(123.45), null, '127.0.0.1');
        $this->_testAddPayment(new DarwinPricing_Client_Price(-123), 'v99');
    }

    /**
     * @expectedException DarwinPricing_Client_Exception_MissingParameter
     * @expectedExceptionMessage Visitor id missing
     */
    public function testAddPayment_BackgroundJob_MissingParameter() {
        unset($_SERVER['REMOTE_ADDR']);
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        $client->addPayment(new DarwinPricing_Client_Price(123.45, 'USD'));
    }

    public function testGetDiscountCode_BackgroundJob() {
        unset($_SERVER['REMOTE_ADDR']);
        $discountCodeMock = array('discount-code' => 'WIN13');
        $visitorId = 99;
        $this->_testGetDiscountCode($discountCodeMock, $visitorId);
    }

    /**
     * @expectedException DarwinPricing_Client_Exception_MissingParameter
     * @expectedExceptionMessage Visitor id missing
     */
    public function testGetDiscountCode_BackgroundJob_MissingParameter() {
        unset($_SERVER['REMOTE_ADDR']);
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        $client->getDiscountCode();
    }

    public function testGetDiscountCode_discountCode() {
        $visitorId = 99;
        foreach (array(
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
        foreach (array(null, 99, '0123') as $visitorId) {
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
     * @expectedException DarwinPricing_Client_Exception_MissingParameter
     * @expectedExceptionMessage Visitor id missing
     */
    public function testGetDynamicPrice_BackgroundJob_MissingParameter() {
        unset($_SERVER['REMOTE_ADDR']);
        $referencePrice = array('value' => 123.45, 'currency' => 'USD');
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        $client->getDynamicPrice(DarwinPricing_Client_Price::fromArray($referencePrice));
    }

    public function testGetDynamicPrice_serverUrl() {
        $dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
        $referencePrice = array('value' => 123.45, 'currency' => 'USD');
        $referencePriceExpected = 'USD123.45';
        $visitorId = 99;
        foreach (array(
    'http://api.darwinpricing.com' => 'http://api.darwinpricing.com',
    'http://api.darwinpricing.com/api' => 'http://api.darwinpricing.com/api',
    'http://api.darwinpricing.com/' => 'http://api.darwinpricing.com',
    'http://api.darwinpricing.com/api/' => 'http://api.darwinpricing.com/api',
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
        foreach (array(null, 99, '0123') as $visitorId) {
            $this->_testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId);
        }
    }

    public function testGetDynamicPrice_referencePrice() {
        $serverUrl = 'http://api.darwinpricing.com';
        $serverUrlExpected = 'http://api.darwinpricing.com';
        $dynamicPriceMock = array('value' => 99.95, 'currency' => 'EUR');
        $visitorId = 99;
        foreach (array(
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
        foreach (array(
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
        $referencePriceExpected = 'USD123.45,-USD123.45,EUR100,10';
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
        $referencePriceExpected = 'USD123.45,-USD123.45,EUR100,10';
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
        $referencePriceExpected = 'USD123.45,-USD123.45,EUR100,10';
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
        $referencePriceExpected = 'USD123.45,-USD123.45,EUR100,10';
        $dynamicPriceListMock = null;
        $this->_testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, 99);

        $referencePriceList = array(
            'p1' => array('value' => 123.45, 'currency' => 'USD'),
            'p2' => array('value' => -123.45, 'currency' => 'USD'),
            'p3' => array('value' => 100, 'currency' => 'EUR'),
            'p4' => array('value' => 10),
        );
        $referencePriceExpected = 'USD123.45,-USD123.45,EUR100,10';
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
     * @expectedException DarwinPricing_Client_Exception_MissingParameter
     * @expectedExceptionMessage Visitor id missing
     */
    public function testGetDynamicPriceList_BackgroundJob_MissingParameter() {
        unset($_SERVER['REMOTE_ADDR']);
        $referencePriceList = array(
            new DarwinPricing_Client_Price(123.45, 'USD'),
            new DarwinPricing_Client_Price(100, 'EUR'),
        );
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc');
        $client->getDynamicPriceList($referencePriceList);
    }

    public function testGetDynamicPriceList_InvalidResponse() {
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc', new DarwinPricing_Client_Visitor(null, '99'));
        $transportMock = $this->_getTransportMock();
        $client->setTransportImplementation($transportMock);
        $parameterListExpected = array(
            'site-id' => 123456,
            'hash' => 'abc',
            'visitor-ip' => '127.0.0.1',
            'visitor-id' => '99',
            'reference-price' => 'USD123.45',
        );
        $optionListExpected = array(
            CURLOPT_URL => 'http://api.darwinpricing.com/get-dynamic-price?' . http_build_query($parameterListExpected),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 3000,
        );
        $transportMock->expects($this->once())->method('_curlExec')->with($optionListExpected)->will($this->returnValue(''));

        $referencePriceList = array(new DarwinPricing_Client_Price(123.45, 'USD'));
        $dynamicPriceListActual = $client->getDynamicPriceList($referencePriceList);
        $this->assertSame($referencePriceList, $dynamicPriceListActual);
    }

    protected function _testAddPayment($profit, $visitorId, $visitorIp = null) {
        foreach (array(
    array('expected' => true, 'curlReturn' => ''),
    array('expected' => false, 'curlReturn' => false),
        ) as $testData) {
            $expected = $testData['expected'];
            $curlReturn = $testData['curlReturn'];
            $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc', new DarwinPricing_Client_Visitor($visitorIp, $visitorId));
            $transportMock = $this->_getTransportMock();
            $client->setTransportImplementation($transportMock);
            $parameterListExpected = array(
                'site-id' => 123456,
                'hash' => 'abc',
            );
            if (null !== $visitorIp) {
                $parameterListExpected['visitor-ip'] = $visitorIp;
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $parameterListExpected['visitor-ip'] = $_SERVER['REMOTE_ADDR'];
            }
            if (null !== $visitorId) {
                $parameterListExpected['visitor-id'] = $visitorId;
            }
            $parameterListExpected['profit'] = (string) $profit;
            $optionListExpected = array(
                CURLOPT_POST => true,
                CURLOPT_URL => 'http://api.darwinpricing.com/add-payment',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT_MS => 3000,
                CURLOPT_POSTFIELDS => http_build_query($parameterListExpected),
            );
            $transportMock->expects($this->once())->method('_curlExec')->with($optionListExpected)->will($this->returnValue($curlReturn));

            $this->assertSame($expected, $client->addPayment($profit, $visitorId));
        }
    }

    protected function _testGetDiscountCode($discountCodeMock, $visitorId, $visitorIp = null) {
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc', new DarwinPricing_Client_Visitor($visitorIp, $visitorId));
        $transportMock = $this->_getTransportMock();
        $client->setTransportImplementation($transportMock);
        $parameterListExpected = array(
            'site-id' => 123456,
            'hash' => 'abc',
        );
        if (null !== $visitorIp) {
            $parameterListExpected['visitor-ip'] = $visitorIp;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $parameterListExpected['visitor-ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (null !== $visitorId) {
            $parameterListExpected['visitor-id'] = $visitorId;
        }
        $optionListExpected = array(
            CURLOPT_URL => 'http://api.darwinpricing.com/get-discount-code?' . http_build_query($parameterListExpected),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 3000,
        );
        $curlReturn = null !== $discountCodeMock ? json_encode($discountCodeMock) : null;
        $transportMock->expects($this->once())->method('_curlExec')->with($optionListExpected)->will($this->returnValue($curlReturn));

        $discountCodeActual = $client->getDiscountCode();
        $this->assertTrue(is_string($discountCodeActual));
        $discountCodeExpected = is_array($discountCodeMock) ? (string) $discountCodeMock['discount-code'] : '';
        $this->assertSame($discountCodeExpected, $discountCodeActual);
    }

    protected function _testGetDynamicPrice($serverUrl, $serverUrlExpected, $dynamicPriceMock, $referencePrice, $referencePriceExpected, $visitorId, $visitorIp = null) {
        $client = new DarwinPricing_Client($serverUrl, 123456, 'abc', new DarwinPricing_Client_Visitor($visitorIp, $visitorId));
        $transportMock = $this->_getTransportMock();
        $client->setTransportImplementation($transportMock);
        $serverUrlExpected = isset($serverUrlExpected) ? $serverUrlExpected : $serverUrl;
        $parameterListExpected = array(
            'site-id' => 123456,
            'hash' => 'abc',
        );
        if (null !== $visitorIp) {
            $parameterListExpected['visitor-ip'] = $visitorIp;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $parameterListExpected['visitor-ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (null !== $visitorId) {
            $parameterListExpected['visitor-id'] = $visitorId;
        }
        $parameterListExpected['reference-price'] = $referencePriceExpected;
        $optionListExpected = array(
            CURLOPT_URL => $serverUrlExpected . '/get-dynamic-price?' . http_build_query($parameterListExpected),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 3000,
        );
        $curlReturn = null !== $dynamicPriceMock ? json_encode($dynamicPriceMock) : null;
        $transportMock->expects($this->once())->method('_curlExec')->with($optionListExpected)->will($this->returnValue($curlReturn));

        $dynamicPriceActual = $client->getDynamicPrice(DarwinPricing_Client_Price::fromArray($referencePrice));
        $this->assertInstanceOf('DarwinPricing_Client_Price', $dynamicPriceActual);
        $dynamicPriceExpected = null !== $dynamicPriceMock ? $dynamicPriceMock : $referencePrice;
        $this->assertSame((float) $dynamicPriceExpected['value'], $dynamicPriceActual->getValue());
        $this->assertSame(isset($dynamicPriceExpected['currency']) ? $dynamicPriceExpected['currency'] : null, $dynamicPriceActual->getCurrency());
    }

    protected function _testGetDynamicPriceList($referencePriceList, $referencePriceExpected, $dynamicPriceListMock, $visitorId, $visitorIp = null) {
        $client = new DarwinPricing_Client('http://api.darwinpricing.com', 123456, 'abc', new DarwinPricing_Client_Visitor($visitorIp, $visitorId));
        $transportMock = $this->_getTransportMock();
        $client->setTransportImplementation($transportMock);
        $parameterListExpected = array(
            'site-id' => 123456,
            'hash' => 'abc',
        );
        if (null !== $visitorIp) {
            $parameterListExpected['visitor-ip'] = $visitorIp;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $parameterListExpected['visitor-ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (null !== $visitorId) {
            $parameterListExpected['visitor-id'] = (string) $visitorId;
        }
        $parameterListExpected['reference-price'] = $referencePriceExpected;
        $optionListExpected = array(
            CURLOPT_URL => 'http://api.darwinpricing.com/get-dynamic-price?' . http_build_query($parameterListExpected),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 3000,
        );
        $curlReturn = null !== $dynamicPriceListMock ? json_encode($dynamicPriceListMock) : null;
        $transportMock->expects($this->once())->method('_curlExec')->with($optionListExpected)->will($this->returnValue($curlReturn));

        $dynamicPriceListActual = $client->getDynamicPriceList(array_map(array(
            'DarwinPricing_Client_Price',
            'fromArray'
                        ), $referencePriceList));
        $this->assertTrue(is_array($dynamicPriceListActual));
        foreach ($dynamicPriceListActual as $dynamicPriceActual) {
            $this->assertInstanceOf('DarwinPricing_Client_Price', $dynamicPriceActual);
        }
        if (null === $dynamicPriceListMock) {
            $dynamicPriceListExpected = $referencePriceList;
        } else {
            $dynamicPriceListExpected = array();
            foreach (array_keys($referencePriceList) as $i => $key) {
                $dynamicPriceListExpected[$key] = $dynamicPriceListMock[$i];
            }
        }
        $this->assertSame(count($dynamicPriceListExpected), count($dynamicPriceListActual));
        foreach ($dynamicPriceListExpected as $key => $dynamicPriceExpected) {
            $this->assertTrue(isset($dynamicPriceListActual[$key]));
            $this->assertSame((float) $dynamicPriceExpected['value'], $dynamicPriceListActual[$key]->getValue());
            $this->assertSame(isset($dynamicPriceExpected['currency']) ? $dynamicPriceExpected['currency'] : null, $dynamicPriceListActual[$key]->getCurrency());
        }
    }

    /**
     * @param mixed $resultMock
     * @return DarwinPricingTest_Client_Transport_CurlMock
     */
    protected function _getTransportMock() {
        $cache = new DarwinPricing_Client_Cache_Local();
        $cache->flush();
        return $this->getMockBuilder('DarwinPricing_Client_Transport_Curl')->setMethods(array('_curlExec'))->getMock();
    }

}
