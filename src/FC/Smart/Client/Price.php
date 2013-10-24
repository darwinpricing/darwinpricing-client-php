<?php

class FC_Smart_Client_Price {

	/** @var string|null */
	protected $_currency = null;

	/** @var float */
	protected $_value;

	/**
	 * @param float       $value
	 * @param string|null $currency
	 */
	public function __construct($value, $currency = null) {
		$this->_value = (float) $value;
		if(null !== $currency) {
			$this->_currency = (string) $currency;
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$currency = $this->getCurrency();
		if(null !== $currency) {
			if($this->getValue() >= 0) {
				return $currency . $this->getValue();
			} else {
				return '-' . $currency . abs($this->getValue());
			}
		} else {
			return (string) $this->getValue();
		}
	}

	/**
	 * @return string|null
	 */
	public function getCurrency() {
		return $this->_currency;
	}

	/**
	 * @return float
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * @param array $price string|null currency, float value
	 *
	 * @throws FC_Smart_Client_Exception_InvalidParameter
	 * @return FC_Smart_Client_Price
	 */
	public static function fromArray($price) {
		if(!is_array($price) || !isset($price['value'])) {
			throw new FC_Smart_Client_Exception_InvalidParameter('Invalid price data array `' . serialize($price) . '`');
		}
		return new FC_Smart_Client_Price($price['value'], isset($price['currency']) ? $price['currency'] : null);
	}
}
