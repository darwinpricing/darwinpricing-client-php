<?php

class DarwinPricing_Client {

    /** @var string */
    protected $_serverUrl;

    /** @var int */
    protected $_clientId;

    /** @var string */
    protected $_clientSecret;

    /** @var DarwinPricing_Client_Visitor|null */
    protected $_visitor;

    /** @var DarwinPricing_Client_Transport_Interface|null */
    protected $_transport;

    /** @var DarwinPricing_Client_Cache_Interface|null */
    protected $_cache;

    /**
     * @param string                            $serverUrl    The URL of the API server for your website, e.g. https://api.darwinpricing.com
     * @param int                               $clientId     The client ID for your website
     * @param string                            $clientSecret The client secret for your website
     * @param DarwinPricing_Client_Visitor|null $visitor      Your website visitor
     */
    public function __construct($serverUrl, $clientId, $clientSecret, DarwinPricing_Client_Visitor $visitor = null) {
        $this->setServerUrl($serverUrl);
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setVisitor($visitor);
    }

    /**
     * @param DarwinPricing_Client_Price $profit Your profit margin for this payment
     * @return bool true on success, false on failure
     */
    public function addPayment(DarwinPricing_Client_Price $profit) {
        $profit = (string) $profit;
        return $this->_addPayment($profit);
    }

    /**
     * @return string The recommended discount code or pricing plan ID for your website visitor (empty by default)
     */
    public function getDiscountCode() {
        $discountCode = $this->_getDiscountCode();
        if (isset($discountCode) && isset($discountCode['discount-code'])) {
            return (string) $discountCode['discount-code'];
        }
        return '';
    }

    /**
     * @return int The recommended discount percentage for your website visitor (0 by default, negative if over the original price)
     */
    public function getDiscountPercent() {
        $discountCode = $this->_getDiscountCode();
        if (isset($discountCode) && isset($discountCode['discount-percent'])) {
            return (int) $discountCode['discount-percent'];
        }
        return 0;
    }

    /**
     * @param DarwinPricing_Client_Price $referencePrice The original price
     * @return DarwinPricing_Client_Price The recommended price for your website visitor
     */
    public function getDynamicPrice(DarwinPricing_Client_Price $referencePrice) {
        $dynamicPrice = $this->_getDynamicPrice((string) $referencePrice);
        if (isset($dynamicPrice)) {
            return DarwinPricing_Client_Price::fromArray($dynamicPrice);
        }
        return $referencePrice;
    }

    /**
     * @param DarwinPricing_Client_Price[] $referencePriceList The original prices by product ID
     * @return DarwinPricing_Client_Price[] The recommended prices by product ID for your website visitor
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
     * @return string URL of the dynamic JavaScript widget (e.g. geo-targeted Exit Intent coupon box)
     */
    public function getWidgetUrl() {
        return $this->_getUrl('/widget') . '?' . http_build_query($this->_getParameterList(true));
    }

    /**
     * @param DarwinPricing_Client_Cache_Interface $cache Your custom cache implementation (optional)
     */
    public function setCacheImplementation(DarwinPricing_Client_Cache_Interface $cache) {
        $this->_cache = $cache;
    }

    /**
     * @param int $clientId The client ID for your website
     */
    public function setClientId($clientId) {
        $clientId = (int) $clientId;
        $this->_clientId = $clientId;
    }

    /**
     * @param string $clientSecret The client secret for your website
     */
    public function setClientSecret($clientSecret) {
        $clientSecret = (string) $clientSecret;
        $this->_clientSecret = $clientSecret;
    }

    /**
     * @param string $serverUrl The URL of the API server for your website
     */
    public function setServerUrl($serverUrl) {
        $serverUrl = (string) $serverUrl;
        $serverUrlFiltered = filter_var($serverUrl, FILTER_VALIDATE_URL);
        if (false === $serverUrlFiltered) {
            throw new DarwinPricing_Client_Exception_InvalidParameter("Invalid server URL `{$serverUrl}`");
        }
        $serverUrlParsed = parse_url($serverUrlFiltered);
        if (isset($serverUrlParsed['query']) || isset($serverUrlParsed['fragment']) || (false !== strpos($serverUrlFiltered, '?')) || (false !== strpos($serverUrlFiltered, '#'))) {
            throw new DarwinPricing_Client_Exception_InvalidParameter("Invalid server URL `{$serverUrl}`");
        }
        $serverUrlFiltered = rtrim($serverUrlFiltered, '/');
        $this->_serverUrl = $serverUrlFiltered;
    }

    /**
     * @param DarwinPricing_Client_Transport_Interface $transport Your custom HTTP transport implementation (optional)
     */
    public function setTransportImplementation(DarwinPricing_Client_Transport_Interface $transport) {
        $this->_transport = $transport;
    }

    /**
     * @param DarwinPricing_Client_Visitor|null $visitor Your website visitor
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
        $result = $this->_httpPost($url, $parameterList);
        return isset($result);
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
        return $this->_httpGetJson($url, $parameterList);
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
        return $this->_httpGetJson($url, $parameterList);
    }

    /**
     * @param bool|null $public
     * @return array
     */
    protected function _getParameterList($public = null) {
        $public = (bool) $public;
        $parameterList = array('site-id' => $this->_clientId);
        if (!$public) {
            $parameterList['hash'] = $this->_clientSecret;
            $this->_getVisitor()->check();
            $visitorIp = $this->_getVisitor()->getIp();
            if ('' !== $visitorIp) {
                $parameterList['visitor-ip'] = $visitorIp;
            }
            $visitorId = $this->_getVisitor()->getId();
            if (null !== $visitorId) {
                $parameterList['visitor-id'] = $visitorId;
            }
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
    protected function _httpGet($url, array $parameterList = null, array $headerList = null) {
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
     * @param string     $url
     * @param array|null $parameterList
     * @param array|null $headerList
     * @return array|null
     */
    protected function _httpGetJson($url, array $parameterList = null, array $headerList = null) {
        $result = $this->_httpGet($url, $parameterList, $headerList);
        if (null === $result) {
            return null;
        }
        $result = json_decode($result, true);
        if (!is_array($result)) {
            return null;
        }
        return $result;
    }

    /**
     * @param string      $url
     * @param array|null  $parameterList
     * @param string|null $body
     * @param array|null  $headerList
     * @return string|null
     */
    protected function _httpPost($url, array $parameterList = null, $body = null, array $headerList = null) {
        $url = (string) $url;
        $parameterList = (array) $parameterList;
        $body = (string) $body;
        $headerList = (array) $headerList;
        return $this->_getTransport()->post($url, $parameterList, $body, $headerList);
    }

}
