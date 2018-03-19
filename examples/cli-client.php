<?php
require_once(dirname(__FILE__) . "/../src/Connect2PayClient.php");
require_once(dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\CartProduct;

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

if (isset($proxy_host) && isset($proxy_port)) {
  $c2pClient->useProxy($proxy_host, $proxy_port);
}

if (isset($validateSSLCertificate) && $validateSSLCertificate == true) {
  $c2pClient->forceSSLValidation();
}

// Do currency conversion if needed
$amount = (isset($defaultAmount)) ? $defaultAmount : 1216;
$currency = (isset($defaultCurrency)) ? $defaultCurrency : "EUR";
if (isset($paymentType) && $currency != 'MXN' && $paymentType == Connect2PayClient::_PAYMENT_TYPE_TODITOCASH) {
  $currency = 'MXN';
  $convertedAmount = $c2pClient->getCurrencyHelper()->convert($amount, "EUR", $currency);

  if ($convertedAmount == null) {
    // There should be a "Plan B" here
    echo "Currency conversion error. Aborting.\n";
    exit(1);
  } else {
    echo "Converted amount from " . ($amount / 100) . " " . $c2pClient->getCurrencyHelper()->getCurrencySymbol("EUR")
    . " to " . ($convertedAmount / 100) . " " . $c2pClient->getCurrencyHelper()->getCurrencySymbol($currency) . ".\n";
    $amount = $convertedAmount;
  }
}

if (isset($subscriptionOfferId)) {
  $c2pClient->setOfferID($subscriptionOfferId);
} elseif (isset($subscription) && is_array($subscription)) {
  if (isset($subscription["subscriptionType"])) {
    $c2pClient->setSubscriptionType($subscription["subscriptionType"]);
  }
  if (isset($subscription["trialPeriod"])) {
    $c2pClient->setTrialPeriod($subscription["trialPeriod"]);
  } else {
    $amount = $subscription["rebillAmount"];
  }
  if (isset($subscription["rebillAmount"])) {
    $c2pClient->setRebillAmount($subscription["rebillAmount"]);
  }
  if (isset($subscription["rebillPeriod"])) {
    $c2pClient->setRebillPeriod($subscription["rebillPeriod"]);
  }
  if (isset($subscription["rebillMaxIteration"])) {
    $c2pClient->setRebillMaxIteration($subscription["rebillMaxIteration"]);
  }
}

// Transaction data
$c2pClient->setOrderID(date("Y-m-d-H.i.s"));

if (isset($paymentType)) {
  $c2pClient->setPaymentType($paymentType);
}
if (isset($operation)) {
  $c2pClient->setOperation($operation);
}
if (isset($provider)) {
  $c2pClient->setProvider($provider);
}
$c2pClient->setPaymentMode(Connect2PayClient::_PAYMENT_MODE_SINGLE);
$c2pClient->setShopperID("1234567");
$c2pClient->setShippingType(Connect2PayClient::_SHIPPING_TYPE_VIRTUAL);
$c2pClient->setAmount($amount);
$c2pClient->setOrderDescription("Test purchase of fake product.");
$c2pClient->setCurrency($currency);
$c2pClient->setShopperFirstName("John");
$c2pClient->setShopperLastName("Doe");
$c2pClient->setShopperAddress("Debit Street, 45");
$c2pClient->setShopperZipcode("3456TRG");
$c2pClient->setShopperCity("New York");
$c2pClient->setShopperState("New York");
$c2pClient->setShopperCountryCode("US");
$c2pClient->setShopperPhone("+34666666666");
$c2pClient->setShopperEmail(isset($shopperEmailAddress) ? $shopperEmailAddress : "shopper@example.com");
$c2pClient->setShopperBirthDate("19700101");
$c2pClient->setShopperIDNumber("ABC-123456");
$c2pClient->setCtrlCustomData("Give that back to me please !!");
if (isset($redirectURL)) {
  $c2pClient->setCtrlRedirectURL($redirectURL);
}
if (isset($callbackURL)) {
  $c2pClient->setCtrlCallbackURL($callbackURL);
}
$c2pClient->setSecure3d(isset($secure3d) ? $secure3d : false);
$c2pClient->setShipToFirstName("Lady");
$c2pClient->setShipToLastName("Gogo");
$c2pClient->setShipToAddress("125 Main Street");
$c2pClient->setShipToZipcode("ABC-5678");
$c2pClient->setShipToState("New York");
$c2pClient->setShipToCity("New York");
$c2pClient->setShipToCountryCode("US");
$c2pClient->setShipToPhone("+47123456789");
$c2pClient->setOrderTotalWithoutShipping($amount - 50);

if (isset($addCartProducts) && $addCartProducts) {
  $product = new CartProduct();
  $product->setCartProductId(1345)->setCartProductName("Test Product");
  $product->setCartProductUnitPrice(456)->setCartProductQuantity(1);
  $product->setCartProductBrand("Yellow Thumb")->setCartProductMPN("NA");
  $product->setCartProductCategoryName("Led screen")->setCartProductCategoryID(1234);
  $c2pClient->addCartProduct($product);

  $product = new CartProduct();
  $product->setCartProductId(6789)->setCartProductName("Test Product 2");
  $product->setCartProductUnitPrice(123)->setCartProductQuantity(1);
  $product->setCartProductBrand("Yellow Thumb")->setCartProductMPN("NA");
  $product->setCartProductCategoryName("DVD reader")->setCartProductCategoryID(1235);
  $c2pClient->addCartProduct($product);
}

if (isset($themeID)) {
  $c2pClient->setThemeID($themeID);
}

if (isset($merchantNotification) && $merchantNotification === true) {
  $c2pClient->setMerchantNotification(true);
  $c2pClient->setMerchantNotificationTo($merchantNotificationTo);
  $c2pClient->setMerchantNotificationLang($merchantNotificationLang);
}

if (isset($affiliateID)) {
  $c2pClient->setAffiliateID($affiliateID);
}
if (isset($campaignName)) {
  $c2pClient->setCampaignName($campaignName);
}

if (isset($timeOut)) {
  $c2pClient->setTimeOut($timeOut);
}

if ($c2pClient->validate()) {
  if ($c2pClient->preparePayment()) {
    echo "Result code:" . $c2pClient->getReturnCode() . "\n";
    echo "Result message:" . $c2pClient->getReturnMessage() . "\n";
    echo "Get merchant status by running: php cli-payment-status.php " . $c2pClient->getMerchantToken() . "\n";
    echo "Customer access is at: " . $c2pClient->getCustomerRedirectURL() . "\n";
    echo "To test the decryption of status posted when the customer is redirected, use the following command:\n";
    echo "php cli-encrypted-status.php " . $c2pClient->getMerchantToken() . ' ${data_field_from_the_form}' . "\n";
  } else {
    echo "Result code:" . $c2pClient->getReturnCode() . "\n";
    echo "Preparation error occured: " . $c2pClient->getClientErrorMessage() . "\n";
  }
} else {
  echo "Validation error occured: " . $c2pClient->getClientErrorMessage() . "\n";
}
