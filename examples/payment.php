<?php
/*
 * This script shows how to create a new payment transaction on the payment page
 * and redirect the customer to it
 */
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\OrderShippingType;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\constant\PaymentMode;
use PayXpert\Connect2Pay\containers\request\PaymentPrepareRequest;
use PayXpert\Connect2Pay\containers\Shopper;
use PayXpert\Connect2Pay\containers\Order;

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

$prepareRequest = new PaymentPrepareRequest();
$shopper = new Shopper();
$order = new Order();

// Setup new payment parameters
$prepareRequest->setPaymentMethod((isset($paymentMethod)) ? $paymentMethod : PaymentMethod::CREDIT_CARD);
if (isset($paymentNetwork)) {
    $prepareRequest->setPaymentNetwork($paymentNetwork);
}
$prepareRequest->setPaymentMode(PaymentMode::SINGLE);
$prepareRequest->setAmount($amount);
$prepareRequest->setCurrency($currency);
$shopper->setId($shopperid);
$shopper->setFirstName("John");
$shopper->setLastName("Doe");
$shopper->setAddress1("Passeig de Gracia, 55");
$shopper->setZipcode("08008");
$shopper->setCity("Barcelona");
$shopper->setState("Barcelona");
$shopper->setCountryCode("ES");
$shopper->setHomePhonePrefix("34");
$shopper->setHomePhone("666666666");
$shopper->setEmail("dev@baian.com");
$prepareRequest->setCtrlRedirectURL($redirectURL);
$prepareRequest->setCtrlCallbackURL($callbackURL);
$order->setId($orderid);
$order->setShippingType(OrderShippingType::DIGITAL_GOODS);
$order->setDescription($product);
// 3DS can be forced or not, nevertheless setting this may have no effect as it
// is also dependent of the merchant configuration,
$prepareRequest->setSecure3d(isset($secure3d) ? $secure3d : false);
// Optional Custom parameters (will be sent back in the payment status)
// $prepareRequest->setCtrlCustomData("Whatever|" . $orderid);

// Merchant email notification can be enabled
$prepareRequest->setMerchantNotification(true);
$prepareRequest->setMerchantNotificationTo("sales@merchant.com");
$prepareRequest->setMerchantNotificationLang("en");


// Create the payment on the payment page
$result = $c2pClient->preparePayment($prepareRequest);
if ($result !== false) {
    // We can save in session the token info returned by the payment page (could
    // be used later when the customer will return from the payment page)
    $_SESSION['merchantToken'] = $result->getMerchantToken();

    // If setup is correct redirect the customer to the payment page.
    header('Location: ' . $c2pClient->getCustomerRedirectURL($result));
} else {
    echo "error prepareTransaction: ";
    echo $c2pClient->getClientErrorMessage() . "\n";
}
