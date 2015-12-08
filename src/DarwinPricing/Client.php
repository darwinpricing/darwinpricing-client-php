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

    /** @var string */
    protected $_visitorIp;

    /**
     * @param string      $serverUrl The URL of your Darwin Pricing api server
     * @param int         $siteId    The ID of your site
     * @param string      $hash      The secret hash code for your site
     * @param string|null $visitorIp The stored IP address of the visitor, for background jobs
     *
     * @throws DarwinPricing_Client_Exception_InvalidParameter
     */
    public function __construct($serverUrl, $siteId, $hash, $visitorIp = null) {
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
        if (null !== $visitorIp) {
            $this->_visitorIp = (string) $visitorIp;
        } else {
            $this->_visitorIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
    }

    /**
     * @param DarwinPricing_Client_Price $profit    Your margin for this purchase (negative for chargebacks)
     * @param string|null           $visitorId The ID of the customer on your system, if any
     *
     * @throws DarwinPricing_Client_Exception_MissingParameter
     * @return bool true on success, false on failure
     */
    public function addPayment(DarwinPricing_Client_Price $profit, $visitorId = null) {
        if ((null === $visitorId) && (null === $this->_visitorIp)) {
            throw new DarwinPricing_Client_Exception_MissingParameter('Missing argument `$visitorId`');
        }
        return $this->_addPayment((string) $profit, $visitorId);
    }

    /**
     * @param string|null $visitorId The ID of the visitor or customer on your system, if any
     *
     * @throws DarwinPricing_Client_Exception_MissingParameter
     * @return string
     */
    public function getDiscountCode($visitorId = null) {
        if ((null === $visitorId) && (null === $this->_visitorIp)) {
            throw new DarwinPricing_Client_Exception_MissingParameter('Missing argument `$visitorId`');
        }
        $discountCode = $this->_getDiscountCode($visitorId);
        if ((null !== $discountCode) && isset($discountCode['discount-code'])) {
            return (string) $discountCode['discount-code'];
        }
        return '';
    }

    /**
     * @param DarwinPricing_Client_Price $referencePrice The original price
     * @param string|null           $visitorId      The ID of the visitor or customer on your system, if any
     *
     * @throws DarwinPricing_Client_Exception_MissingParameter
     * @return DarwinPricing_Client_Price
     */
    public function getDynamicPrice(DarwinPricing_Client_Price $referencePrice, $visitorId = null) {
        if ((null === $visitorId) && (null === $this->_visitorIp)) {
            throw new DarwinPricing_Client_Exception_MissingParameter('Missing argument `$visitorId`');
        }
        $dynamicPrice = $this->_getDynamicPrice((string) $referencePrice, $visitorId);
        if (null !== $dynamicPrice) {
            return DarwinPricing_Client_Price::fromArray($dynamicPrice);
        }
        return $referencePrice;
    }

    /**
     * @param DarwinPricing_Client_Price[] $referencePriceList The original prices
     * @param string|null             $visitorId          The ID of the visitor or customer on your system, if any
     *
     * @throws DarwinPricing_Client_Exception_InvalidParameter
     * @throws DarwinPricing_Client_Exception_MissingParameter
     * @return DarwinPricing_Client_Price[]
     */
    public function getDynamicPriceList($referencePriceList, $visitorId = null) {
        if (!is_array($referencePriceList)) {
            throw new DarwinPricing_Client_Exception_InvalidParameter('Invalid reference price list `' . serialize($referencePriceList) . '`');
        }
        if ((null === $visitorId) && (null === $this->_visitorIp)) {
            throw new DarwinPricing_Client_Exception_MissingParameter('Missing argument `$visitorId`');
        }
        $referencePrices = implode(',', $referencePriceList);
        $dynamicPrices = $this->_getDynamicPrice($referencePrices, $visitorId);
        if (null !== $dynamicPrices) {
            $i = 0;
            foreach ($referencePriceList as $key => $referencePrice) {
                if (isset($dynamicPrices[$i])) {
                    $referencePriceList[$key] = DarwinPricing_Client_Price::fromArray($dynamicPrices[$i]);
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
     * @param string      $profit
     * @param string|null $visitorId
     *
     * @return bool
     */
    protected function _addPayment($profit, $visitorId = null) {
        $profit = (string) $profit;
        if (null !== $visitorId) {
            $visitorId = (string) $visitorId;
        }
        $parameterList = array(
            'site-id' => $this->_siteId,
            'hash' => $this->_hash,
            'profit' => $profit,
        );
        if (null !== $this->_visitorIp) {
            $parameterList['visitor-ip'] = $this->_visitorIp;
        }
        if (null !== $visitorId) {
            $parameterList['visitor-id'] = $visitorId;
        }
        $url = $this->_serverUrl . '/add-payment';
        $result = $this->_getTransport()->post($url, $parameterList);
        return null !== $result;
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
     * @param string|null $visitorId
     *
     * @return array|null
     */
    protected function _getDiscountCode($visitorId = null) {
        if (null !== $visitorId) {
            $visitorId = (string) $visitorId;
        }
        $parameterList = array(
            'site-id' => $this->_siteId,
            'hash' => $this->_hash,
        );
        if (null !== $this->_visitorIp) {
            $parameterList['visitor-ip'] = $this->_visitorIp;
        }
        if (null !== $visitorId) {
            $parameterList['visitor-id'] = (string) $visitorId;
        }
        $url = $this->_serverUrl . '/get-discount-code';
        $result = $this->_getTransport()->get($url, $parameterList);
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
     * @param string      $referencePrice
     * @param string|null $visitorId
     *
     * @return array|null
     */
    protected function _getDynamicPrice($referencePrice, $visitorId = null) {
        $referencePrice = (string) $referencePrice;
        if (null !== $visitorId) {
            $visitorId = (string) $visitorId;
        }
        $parameterList = array(
            'site-id' => $this->_siteId,
            'hash' => $this->_hash,
            'reference-price' => $referencePrice,
        );
        if (null !== $this->_visitorIp) {
            $parameterList['visitor-ip'] = $this->_visitorIp;
        }
        if (null !== $visitorId) {
            $parameterList['visitor-id'] = (string) $visitorId;
        }
        $url = $this->_serverUrl . '/get-dynamic-price';
        $result = $this->_getTransport()->get($url, $parameterList);
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
     * @return DarwinPricing_Client_Transport_Interface
     */
    protected function _getTransport() {
        if (!isset($this->_transport)) {
            $this->setTransportImplementation(new DarwinPricing_Client_Transport_Curl());
        }
        return $this->_transport;
    }

}
