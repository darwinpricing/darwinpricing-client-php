# Darwin Pricing
PHP Client for the dynamic geo-pricing engine [Darwin Pricing](https://www.darwinpricing.com)

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

For a front-end integration using a geo-targeted coupon box, retrieve the URL of the front-end script:
```php
$widgetUrl = $darwinPricing->getWidgetUrl();
```

Then load this script asynchronously on your website:
```html
<script>(function(d,t,s,f){s=d.createElement(t);s.src=<?php echo json_encode($widgetUrl); ?>;s.async=1;f=d.getElementsByTagName(t)[0];f.parentNode.insertBefore(s,f)})(document,'script')</script>
```

For a back-end integration using geo-targeted coupon codes resp. pricing plans, retrieve the discount percentage and the coupon code resp. the code of the pricing plan with:
```php
$discountPercent = $darwinPricing->getDiscountPercent();
$discountCode = $darwinPricing->getDiscountCode();
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
