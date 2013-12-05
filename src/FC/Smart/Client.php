<?php

class FC_Smart_Client {

	/** @var string */
	protected $_hash;

	/** @var string */
	protected $_serverUrl;

	/** @var int */
	protected $_siteId;

	/** @var string */
	protected $_visitorIp;

	/**
	 * @param string      $serverUrl The URL of your Smart Prices Localizer server
	 * @param int         $siteId    The ID of your site
	 * @param string      $hash      The secret hash code for your site
	 * @param string|null $visitorIp The stored IP address of the visitor, for background jobs
	 *
	 * @throws FC_Smart_Client_Exception_InvalidParameter
	 */
	public function __construct($serverUrl, $siteId, $hash, $visitorIp = null) {
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
		$this->_siteId = (int) $siteId;
		$this->_hash = (string) $hash;
		if(null !== $visitorIp) {
			$this->_visitorIp = (string) $visitorIp;
		} else {
			$this->_visitorIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		}
	}

	/**
	 * @param FC_Smart_Client_Price $profit    Your margin for this purchase (negative for chargebacks)
	 * @param string|null           $visitorId The ID of the customer on your system, if any
	 *
	 * @throws FC_Smart_Client_Exception_MissingParameter
	 * @return bool true on success, false on failure
	 */
	public function addPayment(FC_Smart_Client_Price $profit, $visitorId = null) {
		if((null === $visitorId) && (null === $this->_visitorIp)) {
			throw new FC_Smart_Client_Exception_MissingParameter('Missing argument `$visitorId`');
		}
		return $this->_addPayment((string) $profit, $visitorId);
	}

	/**
	 * @param string|null $visitorId The ID of the visitor or customer on your system, if any
	 *
	 * @throws FC_Smart_Client_Exception_MissingParameter
	 * @return string
	 */
	public function getDiscountCode($visitorId = null) {
		if((null === $visitorId) && (null === $this->_visitorIp)) {
			throw new FC_Smart_Client_Exception_MissingParameter('Missing argument `$visitorId`');
		}
		$discountCode = $this->_getDiscountCode($visitorId);
		if((null !== $discountCode) && isset($discountCode['discount-code'])) {
			return (string) $discountCode['discount-code'];
		}
		return '';
	}

	/**
	 * @param FC_Smart_Client_Price $referencePrice The original price
	 * @param string|null           $visitorId      The ID of the visitor or customer on your system, if any
	 *
	 * @throws FC_Smart_Client_Exception_MissingParameter
	 * @return FC_Smart_Client_Price
	 */
	public function getDynamicPrice(FC_Smart_Client_Price $referencePrice, $visitorId = null) {
		if((null === $visitorId) && (null === $this->_visitorIp)) {
			throw new FC_Smart_Client_Exception_MissingParameter('Missing argument `$visitorId`');
		}
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
	 * @throws FC_Smart_Client_Exception_MissingParameter
	 * @return FC_Smart_Client_Price[]
	 */
	public function getDynamicPriceList($referencePriceList, $visitorId = null) {
		if(!is_array($referencePriceList)) {
			throw new FC_Smart_Client_Exception_InvalidParameter('Invalid reference price list `' . serialize($referencePriceList) . '`');
		}
		if((null === $visitorId) && (null === $this->_visitorIp)) {
			throw new FC_Smart_Client_Exception_MissingParameter('Missing argument `$visitorId`');
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
		if(null !== $visitorId) {
			$visitorId = (string) $visitorId;
		}
		$parameterList = array(
			'site-id'    => $this->_siteId,
			'hash'       => $this->_hash,
			'visitor-ip' => $this->_visitorIp,
			'profit'     => (string) $profit,
			'visitor-id' => $visitorId,
		);
		$url = $this->_serverUrl . '/add-payment.php';
		return $this->_httpPost($url, $parameterList);
	}

	/**
	 * @param resource $ch
	 *
	 * @codeCoverageIgnore
	 * @return mixed
	 */
	protected function _curlExec($ch) {
		return curl_exec($ch);
	}

	/**
	 * @param string|null $visitorId
	 *
	 * @return array|null
	 */
	protected function _getDiscountCode($visitorId = null) {
		$parameterList = array(
			'site-id'    => $this->_siteId,
			'hash'       => $this->_hash,
			'visitor-ip' => $this->_visitorIp,
		);
		if(null !== $visitorId) {
			$parameterList['visitor-id'] = (string) $visitorId;
		}
		$url = $this->_serverUrl . '/get-discount-code.php?' . http_build_query($parameterList);
		$result = $this->_httpGet($url);
		if(null === $result) {
			return null;
		}
		$discountCode = json_decode($result, true);
		if(!is_array($discountCode)) {
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
		$parameterList = array(
			'site-id'         => $this->_siteId,
			'hash'            => $this->_hash,
			'visitor-ip'      => $this->_visitorIp,
			'reference-price' => (string) $referencePrice,
		);
		if(null !== $visitorId) {
			$parameterList['visitor-id'] = (string) $visitorId;
		}
		$url = $this->_serverUrl . '/get-dynamic-price.php?' . http_build_query($parameterList);
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
			                            CURLOPT_TIMEOUT_MS     => 3000,
			                       ));
			$result = $this->_curlExec($ch);
			curl_close($ch);
			if(!is_string($result)) {
				return null;
			}
			FC_Smart_Client_Cache::set($cacheKey, $result);
		}
		return $result;
	}

	/**
	 * @param string $url
	 * @param array  $parameterList
	 *
	 * @return bool
	 */
	protected function _httpPost($url, $parameterList) {
		$url = (string) $url;
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
		                            CURLOPT_POST       => true,
		                            CURLOPT_POSTFIELDS => http_build_query($parameterList),
		                            CURLOPT_TIMEOUT_MS => 3000,
		                       ));
		$result = $this->_curlExec($ch);
		curl_close($ch);
		return $result;
	}
}
