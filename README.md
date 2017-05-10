# Darwin Pricing
PHP Client for the dynamic geo-pricing engine [Darwin Pricing](https://www.darwinpricing.com) by [SPOSEA](https://www.sposea.com)

[![Build Status](https://travis-ci.org/darwinpricing/darwinpricing-client-php.svg?branch=master)](https://travis-ci.org/darwinpricing/darwinpricing-client-php)

## Basic Usage

Create a free account on [Darwin Pricing](https://admin.darwinpricing.com).

Instantiate the Darwin Pricing client with your API credentials:
```php
$serverUrl = 'https://api2.darwinpricing.com'; // Use the URL of your API server
$clientId = 50000; // Use the client ID of your Darwin Pricing account
$clientSecret = 'abcdef'; // Use the client secret of your Darwin Pricing account
$darwinPricing = new \DarwinPricing_Client($serverUrl, $clientId, $clientSecret);
```

In order to add our geo-targeted coupon box to your storefront, retrieve the URL of the front-end script:
```php
$widgetUrl = $darwinPricing->getWidgetUrl();
```

Then load this script asynchronously on your website:
```html
<script>(function(d,t,s,f){s=d.createElement(t);s.src=<?php echo json_encode($widgetUrl); ?>;s.async=1;f=d.getElementsByTagName(t)[0];f.parentNode.insertBefore(s,f)})(document,'script')</script>
```

To track payments and payment reversals, use:
```php
$customerIp = '134.3.197.187'; // Use the IP address of your customer
$customer = new \DarwinPricing_Client_Visitor($customerIp);
$darwinPricing->setVisitor($customer);

$profitAmount = 12.34; // Compute your actual net profit for this payment
$profitCurrencyCode = 'USD'; // Use the 3 letters code according to ISO 4217
$profit = new \DarwinPricing_Client_Price($profitAmount, $profitCurrencyCode);
$darwinPricing->addPayment($profit);
```

Darwin Pricing optimizes automatically your geo-pricing strategy in order to maximize your total net profit.
But you can also maximize any other KPI of your choice, depending on your current business goals.
Just send us the appropriate metric instead of your net profit, and your geo-pricing strategy will be optimized accordingly.

## Order Details

Instead of computing your net profits, you can also send directly the full order details to your Darwin Pricing account.
Net profits will then be computed on your behalf.
This will also unlock enhanced reporting capabilities in your Darwin Pricing account, like profit margins and coupon code usage.

In order to track new orders, use:
```php
$order = new DarwinPricing_Client_Order();
$order->setCustomerIp('134.3.197.187'); // The IP address of your customer
$order->setCustomerId('#12321'); // Your reference for this customer (optional)
$order->setEmail('customer@example.com'); // The e-mail address of this customer (optional)

$order->setOrderId('12345'); // The internal ID of this order in your eCommerce system
$order->setOrderReference('#201612345'); // Your reference for this order (optional)
$order->setCurrency('USD'); // The currency code for this order (3 letters code according to ISO 4217)
// For each item sold:
//  - The unit price of this item (including VAT when applicable)
//  - The number of items sold for this order
//  - Your SKU for this item (optional)
//  - The item's internal product ID in your eCommerce system (optional)
//  - The item's internal variant ID in your eCommerce system (optional)
//  - Your average unit costs to purchase or produce this item (optional)
//  - The Value Added Tax rate in percent for this item (when applicable)
$order->addItem(120.90, 3, 'A1234', '123', '456', 89.95, 19.5);
$order->addCoupon('HAPPY10'); // The coupon code redeemed for this order (optional)
$order->setShippingAmount(9.99); // The shipping costs billed to your customer (optional, including VAT when applicable)
$order->setShippingVatRate(12.8); // The Value Added Tax rate in percent for the shipping costs (when applicable)
$order->setTaxes(38.85); // The amount of sales tax (not VAT) for this order (when applicable)
$order->setTotal(375.54); // The total amount billed to your customer (including taxes)

$darwinPricing->trackOrder($order);
```

The customer's e-mail address is only being used to serve geo-targeted newsletter banners in case they are being loaded through an image proxy, like in Google Mail.

## Custom integration

Instead of adding our geo-targeted coupon box to your storefront, you can also retrieve the geo-targeted coupon code from the backend of your store with:
```php
$visitorIp = '134.3.197.187'; // Use the IP address of your visitor
$visitor = new \DarwinPricing_Client_Visitor($visitorIp);
$darwinPricing->setVisitor($visitor);

$discountPercent = $darwinPricing->getDiscountPercent();
$discountCode = $darwinPricing->getDiscountCode();
```
This is especially useful if you want to adjust directly your prices to the local market without using a coupon code for this purpose.
If you are selling a subscription-based product, you can also retrieve a pricing plan identifier instead of a discount code.
Contact us at support@darwinpricing.com to find out the best solution to fit your needs!
