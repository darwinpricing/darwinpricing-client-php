<?php

class DarwinPricing_Client_Price {

    /** @var string|null */
    protected $_currency;

    /** @var float */
    protected $_value;

    /**
     * @param float       $value    Money value, positive for profits, negative for losses
     * @param string|null $currency Currency code (3 letters code according to ISO 4217)
     */
    public function __construct($value, $currency = null) {
        $value = (float) $value;
        if (null !== $currency) {
            $currency = (string) $currency;
        }
        $this->_value = $value;
        $this->_currency = $currency;
    }

    /**
     * @return string
     */
    public function __toString() {
        $currency = $this->getCurrency();
        if (null !== $currency) {
            if ($this->getValue() >= 0) {
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
     * @param array $price float value, string|null currency
     * @return DarwinPricing_Client_Price
     */
    public static function fromArray($price) {
        if (!is_array($price) || !isset($price['value'])) {
            throw new DarwinPricing_Client_Exception_InvalidParameter('Invalid price data array `' . serialize($price) . '`');
        }
        return new DarwinPricing_Client_Price($price['value'], isset($price['currency']) ? $price['currency'] : null);
    }

}
