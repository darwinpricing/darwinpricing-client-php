<?php

class FC_Smart_Client {

	/** @var int */
	protected $_customerId;

	/** @var string */
	protected $_hash;

	/** @var string */
	protected $_serverUrl;

	/** @var int */
	protected $_siteId;

	/** @var string */
	protected $_visitorIp;

	/**
	 * @param string $serverUrl  The URL of your Smart Prices Localizer server
	 * @param int    $customerId The ID of your customer account
	 * @param int    $siteId     The ID of your site
	 * @param string $hash       The secret hash code for your site
	 *
	 * @throws FC_Smart_Client_Exception_InvalidParameter
	 */
	public function __construct($serverUrl, $customerId, $siteId, $hash) {
		$serverUrlFiltered = filter_var((string) $serverUrl, FILTER_VALIDATE_URL);
		if(false === $serverUrlFiltered) {
			throw new FC_Smart_Client_Exception_InvalidParameter("Invalid server URL `$serverUrl`");
		}
		$serverUrlParsed = parse_url($serverUrlFiltered);
		if(isset($serverUrlParsed['query']) || isset($serverUrlParsed['fragment']) || (false !== strpos($serverUrlFiltered, '?')) || (false !== strpos($serverUrlFiltered, '#'))) {
			throw new FC_Smart_Client_Exception_InvalidParameter("Invalid server URL `$serverUrl`");
		}
		if(substr($serverUrlFiltered, -1) === '/') {
			$serverUrlFiltered = substr($serverUrlFiltered, 0, -1);
		}
		$this->_serverUrl = $serverUrlFiltered;
		$this->_customerId = (int) $customerId;
		$this->_siteId = (int) $siteId;
		$this->_hash = (string) $hash;
		$this->_visitorIp = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * @param FC_Smart_Client_Price $profit
	 * @param null                  $visitorId
	 *
	 * @return bool
	 */
	public function addPayment(FC_Smart_Client_Price $profit, $visitorId = null) {
		return $this->_addPayment((string) $profit, $visitorId);
	}

	/**
	 * @param FC_Smart_Client_Price $referencePrice The original price
	 * @param string|null           $visitorId      The ID of the visitor or customer on your system, if any
	 *
	 * @throws FC_Smart_Client_Exception_InvalidParameter
	 * @return FC_Smart_Client_Price
	 */
	public function getDynamicPrice(FC_Smart_Client_Price $referencePrice, $visitorId = null) {
		$dynamicPrice = $this->_getDynamicPrice((string) $referencePrice, $visitorId);
		if(null !== $dynamicPrice) {
			return FC_Smart_Client_Price::fromArray($dynamicPrice);
		}
		return $referencePrice;
	}

	/**
	 * @param FC_Smart_Client_Price[] $referencePriceList The original prices
	 * @param string|null             $visitorId          The ID of the visitor or customer on your system, if any
	 *
	 * @throws FC_Smart_Client_Exception_InvalidParameter
	 * @return FC_Smart_Client_Price[]
	 */
	public function getDynamicPriceList($referencePriceList, $visitorId = null) {
		if(!is_array($referencePriceList)) {
			throw new FC_Smart_Client_Exception_InvalidParameter('Invalid reference price list `' . serialize($referencePriceList) . '`');
		}
		$referencePrices = implode(',', $referencePriceList);
		$dynamicPrices = $this->_getDynamicPrice($referencePrices, $visitorId);
		if(null !== $dynamicPrices) {
			$i = 0;
			foreach($referencePriceList as $key => $referencePrice) {
				if(isset($dynamicPrices[$i])) {
					$referencePriceList[$key] = FC_Smart_Client_Price::fromArray($dynamicPrices[$i]);
				}
				$i++;
			}
		}
		return $referencePriceList;
	}

	/**
	 * @param string      $profit
	 * @param string|null $visitorId
	 *
	 * @return bool
	 */
	protected function _addPayment($profit, $visitorId = null) {
		$parameterList = array(
			'customer-id' => $this->_customerId,
			'site-id'     => $this->_siteId,
			'hash'        => $this->_hash,
			'visitor-ip'  => $this->_visitorIp,
			'profit'      => (string) $profit,
		);
		if(null !== $visitorId) {
			$parameterList['visitor-id'] = (string) $visitorId;
		}
		$url = $this->_serverUrl . '/add-payment?' . http_build_query($parameterList);
		return $this->_httpPost($url);
	}

	/**
	 * @param resource $ch
	 *
	 * @return mixed
	 */
	protected function _curlExec($ch) {
		return curl_exec($ch);
	}

	/**
	 * @param string      $referencePrice
	 * @param string|null $visitorId
	 *
	 * @return array|null
	 */
	protected function _getDynamicPrice($referencePrice, $visitorId = null) {
		$parameterList = array(
			'customer-id'     => $this->_customerId,
			'site-id'         => $this->_siteId,
			'hash'            => $this->_hash,
			'visitor-ip'      => $this->_visitorIp,
			'reference-price' => (string) $referencePrice,
		);
		if(null !== $visitorId) {
			$parameterList['visitor-id'] = (string) $visitorId;
		}
		$url = $this->_serverUrl . '/get-dynamic-price?' . http_build_query($parameterList);
		$result = $this->_httpGet($url);
		if(null === $result) {
			return null;
		}
		$dynamicPrice = json_decode($result, true);
		if(!is_array($dynamicPrice)) {
			return null;
		}
		return $dynamicPrice;
	}

	/**
	 * @param string $url
	 *
	 * @return string|null
	 */
	protected function _httpGet($url) {
		$url = (string) $url;
		$cacheKey = __CLASS__ . '::' . __METHOD__ . '(' . $url . ')';
		$result = FC_Smart_Client_Cache::get($cacheKey);
		if(false === $result) {
			$ch = curl_init($url);
			curl_setopt_array($ch, array(
			                            CURLOPT_RETURNTRANSFER => true,
			                            CURLOPT_TIMEOUT_MS     => 500,
			                       ));
			$result = $this->_curlExec($ch);
			if(!is_string($result)) {
				return null;
			}
			FC_Smart_Client_Cache::set($cacheKey, $result);
		}
		return $result;
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function _httpPost($url) {
		$url = (string) $url;
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
		                            CURLOPT_POST       => true,
		                            CURLOPT_TIMEOUT_MS => 500,
		                       ));
		return $this->_curlExec($ch);
	}
}
