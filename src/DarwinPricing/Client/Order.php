<?php

class DarwinPricing_Client_Order {

    /** @var string|null */
    protected $_currency, $_customerId, $_customerIp, $_email, $_orderId, $_orderReference;

    /** @var float|null */
    protected $_shippingAmount, $_shippingVatRate, $_taxes, $_total;

    /** @var string[]|null */
    protected $_couponList;

    /** @var array|null */
    protected $_itemList;

    /**
     * @param string $couponCode
     */
    public function addCoupon($couponCode) {
        $couponCode = (string) $couponCode;
        $couponList = (array) $this->_getCouponList();
        $couponList[] = $couponCode;
        $this->_setCouponList($couponList);
    }

    /**
     * @param float       $unitPrice
     * @param int         $quantity
     * @param string|null $sku
     * @param string|null $productId
     * @param string|null $variantId
     * @param float|null  $unitCost
     * @param float|null  $vatRate
     */
    public function addItem($unitPrice, $quantity, $sku = null, $productId = null, $variantId = null, $unitCost = null, $vatRate = null) {
        $unitPrice = (float) $unitPrice;
        $quantity = (int) $quantity;
        if (null !== $sku) {
            $sku = (string) $sku;
        }
        if (null !== $productId) {
            $productId = (string) $productId;
        }
        if (null !== $variantId) {
            $variantId = (string) $variantId;
        }
        if (null !== $unitCost) {
            $unitCost = (float) $unitCost;
        }
        if (null !== $vatRate) {
            $vatRate = (float) $vatRate;
        }
        $item = array(
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
        );
        if (null !== $sku) {
            $item['sku'] = $sku;
        }
        if (null !== $productId) {
            $item['product_id'] = $productId;
        }
        if (null !== $variantId) {
            $item['variant_id'] = $variantId;
        }
        if (null !== $unitCost) {
            $item['unit_cost'] = $unitCost;
        }
        if (null !== $vatRate) {
            $item['vat_rate'] = $vatRate;
        }
        $itemList = (array) $this->_getItemList();
        $itemList[] = $item;
        $this->_setItemList($itemList);
    }

    /**
     * @return string|null
     */
    public function getCurrency() {
        return $this->_currency;
    }

    /**
     * @param string|null $currency
     */
    public function setCurrency($currency) {
        if (null !== $currency) {
            $currency = (string) $currency;
        }
        $this->_currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getCustomerId() {
        return $this->_customerId;
    }

    /**
     * @param string|null $customerId
     */
    public function setCustomerId($customerId) {
        if (null !== $customerId) {
            $customerId = (string) $customerId;
        }
        $this->_customerId = $customerId;
    }

    /**
     * @return string|null
     */
    public function getCustomerIp() {
        return $this->_customerIp;
    }

    /**
     * @param string|null $customerIp
     */
    public function setCustomerIp($customerIp) {
        if (null !== $customerIp) {
            $customerIp = (string) $customerIp;
        }
        $this->_customerIp = $customerIp;
    }

    /**
     * @return string|null
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail($email) {
        if (null !== $email) {
            $email = (string) $email;
        }
        $this->_email = $email;
    }

    /**
     * @return string|null
     */
    public function getOrderId() {
        return $this->_orderId;
    }

    /**
     * @param string|null $orderId
     */
    public function setOrderId($orderId) {
        if (null !== $orderId) {
            $orderId = (string) $orderId;
        }
        $this->_orderId = $orderId;
    }

    /**
     * @return string|null
     */
    public function getOrderReference() {
        return $this->_orderReference;
    }

    /**
     * @param string|null $orderReference
     */
    public function setOrderReference($orderReference) {
        if (null !== $orderReference) {
            $orderReference = (string) $orderReference;
        }
        $this->_orderReference = $orderReference;
    }

    /**
     * @return float|null
     */
    public function getShippingAmount() {
        return $this->_shippingAmount;
    }

    /**
     * @param float|null $shippingAmount
     */
    public function setShippingAmount($shippingAmount) {
        if (null !== $shippingAmount) {
            $shippingAmount = (float) $shippingAmount;
        }
        $this->_shippingAmount = $shippingAmount;
    }

    /**
     * @return float|null
     */
    public function getShippingVatRate() {
        return $this->_shippingVatRate;
    }

    /**
     * @param float|null $shippingVatRate
     */
    public function setShippingVatRate($shippingVatRate) {
        if (null !== $shippingVatRate) {
            $shippingVatRate = (float) $shippingVatRate;
        }
        $this->_shippingVatRate = $shippingVatRate;
    }

    /**
     * @return float|null
     */
    public function getTaxes() {
        return $this->_taxes;
    }

    /**
     * @param float|null $taxes
     */
    public function setTaxes($taxes) {
        if (null !== $taxes) {
            $taxes = (float) $taxes;
        }
        $this->_taxes = $taxes;
    }

    /**
     * @return float|null
     */
    public function getTotal() {
        return $this->_total;
    }

    /**
     * @param float|null $total
     */
    public function setTotal($total) {
        if (null !== $total) {
            $total = (float) $total;
        }
        $this->_total = $total;
    }

    /**
     * @return array
     */
    public function toArray() {
        $data = array(
            'coupon_list' => $this->_getCouponList(),
            'currency' => $this->getCurrency(),
            'customer_id' => $this->getCustomerId(),
            'customer_ip' => $this->getCustomerIp(),
            'email' => $this->getEmail(),
            'item_list' => $this->_getItemList(),
            'order_id' => $this->getOrderId(),
            'order_reference' => $this->getOrderReference(),
            'shipping_amount' => $this->getShippingAmount(),
            'shipping_vat_rate' => $this->getShippingVatRate(),
            'taxes' => $this->getTaxes(),
            'total' => $this->getTotal(),
        );
        return array_filter($data, array($this, '_isNotNull'));
    }

    /**
     * @return string
     */
    public function toJson() {
        $data = $this->toArray();
        if (empty($data)) {
            $data = new stdClass();
        }
        return json_encode($data);
    }

    public function __toString() {
        return $this->toJson();
    }

    /**
     * @return string[]|null
     */
    protected function _getCouponList() {
        return $this->_couponList;
    }

    /**
     * @param string[]|null $couponList
     */
    protected function _setCouponList(array $couponList = null) {
        $this->_couponList = $couponList;
    }

    /**
     * @return array|null
     */
    protected function _getItemList() {
        return $this->_itemList;
    }

    /**
     * @param array|null $itemList
     */
    protected function _setItemList(array $itemList = null) {
        $this->_itemList = $itemList;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isNotNull($value) {
        return null !== $value;
    }

}
