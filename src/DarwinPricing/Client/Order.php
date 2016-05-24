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
     * @param string $couponCode The coupon code redeemed for this order
     */
    public function addCoupon($couponCode) {
        $couponCode = (string) $couponCode;
        $couponList = (array) $this->_getCouponList();
        $couponList[] = $couponCode;
        $this->_setCouponList($couponList);
    }

    /**
     * @param float       $unitPrice The unit price of this item (including VAT when applicable)
     * @param int         $quantity  The number of items sold for this order
     * @param string|null $sku       Your SKU for this item
     * @param string|null $productId The item's internal product ID in your eCommerce system
     * @param string|null $variantId The item's internal variant ID in your eCommerce system
     * @param float|null  $unitCost  Your average unit costs to purchase or produce this item
     * @param float|null  $vatRate   The Value Added Tax rate in percent for this item (when applicable)
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
     * @param string|null $currency The currency code for this order (3 letters code according to ISO 4217)
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
     * @param string|null $customerId Your reference for this customer
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
     * @param string|null $customerIp The IP address of this customer
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
     * @param string|null $email The e-mail address of this customer
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
     * @param string|null $orderId The internal ID of this order in your eCommerce system
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
     * @param string|null $orderReference Your reference for this order
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
     * @param float|null $shippingAmount The shipping costs billed to your customer (including VAT when applicable)
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
     * @param float|null $shippingVatRate The Value Added Tax rate in percent for the shipping costs (when applicable)
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
     * @param float|null $taxes The amount of sales tax for this order (not VAT)
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
     * @param float|null $total The total amount billed to your customer (including taxes)
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
