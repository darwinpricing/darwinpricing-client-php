<?php

class DarwinPricing_Client {

    /** @var DarwinPricing_Client_Cache_Interface|null */
    protected $_cache;

    /** @var string */
    protected $_hash;

    /** @var string */
    protected $_serverUrl;

    /** @var int */
    protected $_siteId;

    /** @var DarwinPricing_Client_Transport_Interface|null */
    protected $_transport;

    /** @var DarwinPricing_Client_Visitor|null */
    protected $_visitor;

    /**
     * @param string $serverUrl The URL of your Darwin Pricing api server
     * @param int    $siteId    The ID of your site
     * @param string $hash      The secret hash code for your site
     */
    public function __construct($serverUrl, $siteId, $hash) {
        $serverUrlFiltered = filter_var((string) $serverUrl, FILTER_VALIDATE_URL);
        if (false === $serverUrlFiltered) {
            throw new DarwinPricing_Client_Exception_InvalidParameter("Invalid server URL `$serverUrl`");
        }
        $serverUrlParsed = parse_url($serverUrlFiltered);
        if (isset($serverUrlParsed['query']) || isset($serverUrlParsed['fragment']) || (false !== strpos($serverUrlFiltered, '?')) || (false !== strpos($serverUrlFiltered, '#'))) {
            throw new DarwinPricing_Client_Exception_InvalidParameter("Invalid server URL `$serverUrl`");
        }
        if (substr($serverUrlFiltered, -1) === '/') {
            $serverUrlFiltered = substr($serverUrlFiltered, 0, -1);
        }
        $this->_serverUrl = $serverUrlFiltered;
        $this->_siteId = (int) $siteId;
        $this->_hash = (string) $hash;
    }

    /**
     * @param DarwinPricing_Client_Price $profit Your margin for this purchase (negative for chargebacks)
     * @return bool true on success, false on failure
     */
    public function addPayment(DarwinPricing_Client_Price $profit) {
        $profit = (string) $profit;
        return $this->_addPayment($profit);
    }

    /**
     * @return string
     */
    public function getDiscountCode() {
        $discountCode = $this->_getDiscountCode();
        if (isset($discountCode) && isset($discountCode['discount-code'])) {
            return (string) $discountCode['discount-code'];
        }
        return '';
    }

    /**
     * @param DarwinPricing_Client_Price $referencePrice The original price
     * @return DarwinPricing_Client_Price
     */
    public function getDynamicPrice(DarwinPricing_Client_Price $referencePrice) {
        $dynamicPrice = $this->_getDynamicPrice((string) $referencePrice);
        if (isset($dynamicPrice)) {
            return DarwinPricing_Client_Price::fromArray($dynamicPrice);
        }
        return $referencePrice;
    }

    /**
     * @param DarwinPricing_Client_Price[] $referencePriceList The original prices
     * @return DarwinPricing_Client_Price[]
     */
    public function getDynamicPriceList(array $referencePriceList) {
        $dynamicPriceList = $this->_getDynamicPrice(implode(',', $referencePriceList));
        if (isset($dynamicPriceList)) {
            $i = 0;
            foreach (array_keys($referencePriceList) as $key) {
                if (isset($dynamicPriceList[$i])) {
                    $referencePriceList[$key] = DarwinPricing_Client_Price::fromArray($dynamicPriceList[$i]);
                }
                $i++;
            }
        }
        return $referencePriceList;
    }

    /**
     * @param DarwinPricing_Client_Cache_Interface $cache
     */
    public function setCacheImplementation(DarwinPricing_Client_Cache_Interface $cache) {
        $this->_cache = $cache;
    }

    /**
     * @param DarwinPricing_Client_Transport_Interface $transport
     */
    public function setTransportImplementation(DarwinPricing_Client_Transport_Interface $transport) {
        $this->_transport = $transport;
    }

    /**
     * @param DarwinPricing_Client_Visitor|null $visitor
     */
    public function setVisitor(DarwinPricing_Client_Visitor $visitor = null) {
        $this->_visitor = $visitor;
    }

    /**
     * @param string $profit
     * @return bool
     */
    protected function _addPayment($profit) {
        $profit = (string) $profit;
        $url = $this->_getUrl('/add-payment');
        $parameterList = $this->_getParameterList();
        $parameterList['profit'] = $profit;
        $result = $this->_post($url, $parameterList);
        return isset($result);
    }

    /**
     * @param string     $url
     * @param array|null $parameterList
     * @param array|null $headerList
     * @return string|null
     */
    protected function _get($url, array $parameterList = null, array $headerList = null) {
        $url = (string) $url;
        $parameterList = (array) $parameterList;
        $headerList = (array) $headerList;
        $cacheKey = __METHOD__ . '(' . serialize($url) . ',' . serialize($parameterList) . ',' . serialize($headerList) . ')';
        $cache = $this->_getCache();
        $result = $cache->get($cacheKey);
        if (false === $result) {
            $result = $this->_getTransport()->get($url, $parameterList, $headerList);
            $cache->set($cacheKey, $result);
        }
        return $result;
    }

    /**
     * @return DarwinPricing_Client_Cache_Interface
     */
    protected function _getCache() {
        if (!isset($this->_cache)) {
            $this->setCacheImplementation(new DarwinPricing_Client_Cache_Local());
        }
        return $this->_cache;
    }

    /**
     * @return array|null
     */
    protected function _getDiscountCode() {
        $url = $this->_getUrl('/get-discount-code');
        $parameterList = $this->_getParameterList();
        $result = $this->_get($url, $parameterList);
        if (null === $result) {
            return null;
        }
        $discountCode = json_decode($result, true);
        if (!is_array($discountCode)) {
            return null;
        }
        return $discountCode;
    }

    /**
     * @param string $referencePrice
     * @return array|null
     */
    protected function _getDynamicPrice($referencePrice) {
        $referencePrice = (string) $referencePrice;
        $url = $this->_getUrl('/get-dynamic-price');
        $parameterList = $this->_getParameterList();
        $parameterList['reference-price'] = $referencePrice;
        $result = $this->_get($url, $parameterList);
        if (null === $result) {
            return null;
        }
        $dynamicPrice = json_decode($result, true);
        if (!is_array($dynamicPrice)) {
            return null;
        }
        return $dynamicPrice;
    }

    /**
     * @return array
     */
    protected function _getParameterList() {
        $parameterList = array(
            'site-id' => $this->_siteId,
            'hash' => $this->_hash,
        );
        $this->_getVisitor()->check();
        $visitorId = $this->_getVisitor()->getId();
        $visitorIp = $this->_getVisitor()->getIp();
        if ('' !== $visitorIp) {
            $parameterList['visitor-ip'] = $visitorIp;
        }
        if (null !== $visitorId) {
            $parameterList['visitor-id'] = $visitorId;
        }
        return $parameterList;
    }

    /**
     * @return DarwinPricing_Client_Transport_Interface
     */
    protected function _getTransport() {
        if (!isset($this->_transport)) {
            $this->setTransportImplementation(new DarwinPricing_Client_Transport_Curl());
        }
        return $this->_transport;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function _getUrl($path) {
        $path = (string) $path;
        return $this->_serverUrl . $path;
    }

    /**
     * @return DarwinPricing_Client_Visitor
     */
    protected function _getVisitor() {
        if (null === $this->_visitor) {
            $this->_visitor = new DarwinPricing_Client_Visitor();
        }
        return $this->_visitor;
    }

    /**
     * @param string     $url
     * @param array|null $parameterList
     * @param array|null $headerList
     * @return string|null
     */
    protected function _post($url, array $parameterList = null, array $headerList = null) {
        $url = (string) $url;
        $parameterList = (array) $parameterList;
        $headerList = (array) $headerList;
        return $this->_getTransport()->post($url, $parameterList, $headerList);
    }

}
