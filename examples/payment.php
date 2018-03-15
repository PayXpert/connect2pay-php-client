<?php
/*
 * This script shows how to create a new payment transaction on the payment page
 * and redirect the customer to it
 */
require_once (dirname(__FILE__) . "/../src/Connect2PayClient.php");
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

session_start();

/*
 * The following data should come from the order handling system of the
 * application
 */
// Amount should be send in cents 100 = 1.00 USD
$amount = 100;
// Currency EUR, GBP, USD...
$currency = "USD";
// Order ID,
$orderid = "11111";
// Description (displayed on the payment page)
$product = "Test purchase of product.";
// Customer id
$shopperid = "123456";

// System setup
$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

// Setup new payment parameters
$c2pClient->setOrderID($orderid);
$c2pClient->setPaymentType((isset($paymentType)) ? $paymentType : Connect2PayClient::_PAYMENT_TYPE_CREDITCARD);
$c2pClient->setPaymentMode(Connect2PayClient::_PAYMENT_MODE_SINGLE);
$c2pClient->setShopperID($shopperid);
$c2pClient->setShippingType(Connect2PayClient::_SHIPPING_TYPE_VIRTUAL);
$c2pClient->setAmount($amount);
$c2pClient->setOrderDescription($product);
$c2pClient->setCurrency($currency);
$c2pClient->setShopperFirstName("John");
$c2pClient->setShopperLastName("Doe");
$c2pClient->setShopperAddress("Passeig de Gracia, 55");
$c2pClient->setShopperZipcode("08008");
$c2pClient->setShopperCity("Barcelona");
$c2pClient->setShopperState("Barcelona");
$c2pClient->setShopperCountryCode("ES");
$c2pClient->setShopperPhone("+34666666666");
$c2pClient->setShopperEmail("dev@baian.com");
$c2pClient->setCtrlRedirectURL($redirectURL);
$c2pClient->setCtrlCallbackURL($callbackURL);
// 3DS can be forced or not, nevertheless setting this may have no effect as it
// is also dependent of the merchant configuration,
$c2pClient->setSecure3d(isset($secure3d) ? $secure3d : false);
// Optional Custom parameters (will be sent back in the payment status)
// $c2pClient->setCtrlCustomData("Whatever|" . $orderid);

// Merchant email notification can be enabled
$c2pClient->setMerchantNotification(true);
$c2pClient->setMerchantNotificationTo("sales@merchant.com");
$c2pClient->setMerchantNotificationLang("en");

// Validate our information
if ($c2pClient->validate()) {
  // Create the payment transaction on the payment page
  if ($c2pClient->prepareTransaction()) {
    // We can save in session the token info returned by the payment page (could
    // be used later when the customer will return from the payment page)
    $_SESSION['merchantToken'] = $c2pClient->getMerchantToken();

    // If setup is correct redirect the customer to the payment page.
    header('Location: ' . $c2pClient->getCustomerRedirectURL());
  } else {
    echo "error prepareTransaction: ";
    echo $c2pClient->getClientErrorMessage() . "\n";
  }
} else {
  echo "error validate: ";
  echo $c2pClient->getClientErrorMessage() . "\n";
}