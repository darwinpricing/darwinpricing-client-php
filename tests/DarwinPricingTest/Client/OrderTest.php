<?php

class DarwinPricingTest_Client_OrderTest extends PHPUnit_Framework_TestCase {

    public function testSetters() {
        $order = new DarwinPricing_Client_Order();
        $this->assertSame('{}', (string) $order);
        $order->addCoupon('HAPPY10');
        $this->assertSame('{"coupon_list":["HAPPY10"]}', (string) $order);
        $order->addCoupon('FREESHIP');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"]}', (string) $order);
        $order->addItem(12.34, 1);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"item_list":[{"unit_price":12.34,"quantity":1}]}', (string) $order);
        $order->addItem(120, 3, 'SKU', 'PID', 'VID', 89.95, 19.5);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}]}', (string) $order);
        $order->setCurrency('USD');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}]}', (string) $order);
        $order->setCustomerId('CID');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}]}', (string) $order);
        $order->setCustomerIp('10.10.10.10');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}]}', (string) $order);
        $order->setEmail('test@example.com');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}]}', (string) $order);
        $order->setOrderId('OID');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID"}', (string) $order);
        $order->setOrderReference('OREF');
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID","order_reference":"OREF"}', (string) $order);
        $order->setShippingAmount(9.99);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID","order_reference":"OREF","shipping_amount":9.99}', (string) $order);
        $order->setShippingVatRate(12.8);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID","order_reference":"OREF","shipping_amount":9.99,"shipping_vat_rate":12.8}', (string) $order);
        $order->setTaxes(38.85);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID","order_reference":"OREF","shipping_amount":9.99,"shipping_vat_rate":12.8,"taxes":38.85}', (string) $order);
        $order->setTotal(383.95);
        $this->assertSame('{"coupon_list":["HAPPY10","FREESHIP"],"currency":"USD","customer_id":"CID","customer_ip":"10.10.10.10","email":"test@example.com","item_list":[{"unit_price":12.34,"quantity":1},{"unit_price":120,"quantity":3,"sku":"SKU","product_id":"PID","variant_id":"VID","unit_cost":89.95,"vat_rate":19.5}],"order_id":"OID","order_reference":"OREF","shipping_amount":9.99,"shipping_vat_rate":12.8,"taxes":38.85,"total":383.95}', (string) $order);
    }

}
