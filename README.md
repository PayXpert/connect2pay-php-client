PayXpert Payment Page client
============================

[![Build Status](https://travis-ci.org/PayXpert/connect2pay-php-client.svg?branch=master)](https://travis-ci.org/PayXpert/connect2pay-php-client)

This library is the official PHP client to interact with the PayXpert Payment Page system.
The whole payment workflow is implemented through easy to use methods.

Installation
------------

Easy installation with Composer is available.
Install with [`composer.phar`](http://getcomposer.org).

```sh
php composer.phar require "payxpert/connect2pay"
```

Basic usage
-----------

The example below shows a simple use to create a Credit Card payment.

```php
use PayXpert\Connect2Pay\Connect2PayClient;

$connect2pay = "https://connect2.payxpert.com/";
// This will be provided once your account is approved
$originator  = "000000";
$password    = "Gr3atPassw0rd!";

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

// Set all information for the payment
$c2pClient->setOrderID("ABC-123456");
$c2pClient->setPaymentType(Connect2PayClient::_PAYMENT_TYPE_CREDITCARD);
$c2pClient->setPaymentMode(Connect2PayClient::_PAYMENT_MODE_SINGLE);
$c2pClient->setShopperID("1234567WX");
$c2pClient->setShippingType(Connect2PayClient::_SHIPPING_TYPE_VIRTUAL);
// To charge €25.99
$c2pClient->setCurrency("EUR");
$c2pClient->setAmount(2599);
$c2pClient->setOrderDescription("Payment of €25.99");
$c2pClient->setShopperFirstName("John");
$c2pClient->setShopperLastName("Doe");
$c2pClient->setShopperAddress("NA");
$c2pClient->setShopperZipcode("NA");
$c2pClient->setShopperCity("NA");
$c2pClient->setShopperCountryCode("GB");
$c2pClient->setShopperPhone("+4712345678");
$c2pClient->setShopperEmail("shopper@example.com");
// Extra custom data that are returned with the payment status
$c2pClient->setCtrlCustomData("Give that back to me please !!");
// Where the customer will be redirected after the payment
$c2pClient->setCtrlRedirectURL("https://merchant.example.com/payment/redirect");
// URL on the merchant site that will receive the callback notification
$c2pClient->setCtrlCallbackURL("https://merchant.example.com/payment/callback");

if ($c2pClient->validate()) {
  if ($c2pClient->preparePayment()) {
    // The customer token info returned by the payment page could be saved in session (may
    // be used later when the customer will be redirected from the payment page)
    $_SESSION['merchantToken'] = $c2pClient->getMerchantToken();

    // The merchantToken must also be used later to validate the callback to avoid that anyone
    // could call it and abusively validate the payment. It may be stored in local database for this.

    // Now redirect the customer to the payment page
    header('Location: ' . $c2pClient->getCustomerRedirectURL());
  } else {
    echo "error prepareTransaction: ";
    echo $c2pClient->getClientErrorMessage() . "\n";
  }
} else {
  echo "Validation error occured: " . $c2pClient->getClientErrorMessage() . "\n";
}
```

See scripts in the examples/ folder to see more use cases.