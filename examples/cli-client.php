<?php
require_once(dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\containers\Account;
use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\CartProduct;
use PayXpert\Connect2Pay\containers\constant\AccountAge;
use PayXpert\Connect2Pay\containers\constant\AccountLastChange;
use PayXpert\Connect2Pay\containers\constant\AccountPwChange;
use PayXpert\Connect2Pay\containers\constant\OrderShippingType;
use PayXpert\Connect2Pay\containers\constant\PaymentMode;
use PayXpert\Connect2Pay\containers\Order;
use PayXpert\Connect2Pay\containers\request\PaymentPrepareRequest;
use PayXpert\Connect2Pay\containers\Shipping;
use PayXpert\Connect2Pay\containers\Shopper;

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

if (isset($proxy_host) && isset($proxy_port)) {
  $c2pClient->useProxy($proxy_host, $proxy_port);
}

$amount = (isset($defaultAmount)) ? $defaultAmount : 1216;
$currency = (isset($defaultCurrency)) ? $defaultCurrency : "EUR";

$prepareRequest = new PaymentPrepareRequest();
$shopper = new Shopper();
$order = new Order();
$shipping = new Shipping();

if (isset($subscriptionOfferId)) {
  $prepareRequest->setOfferID($subscriptionOfferId);
} elseif (isset($subscription) && is_array($subscription)) {
  if (isset($subscription["subscriptionType"])) {
    $prepareRequest->setSubscriptionType($subscription["subscriptionType"]);
  }
  if (isset($subscription["trialPeriod"])) {
    $prepareRequest->setTrialPeriod($subscription["trialPeriod"]);
  } else {
    $amount = $subscription["rebillAmount"];
  }
  if (isset($subscription["rebillAmount"])) {
    $prepareRequest->setRebillAmount($subscription["rebillAmount"]);
  }
  if (isset($subscription["rebillPeriod"])) {
    $prepareRequest->setRebillPeriod($subscription["rebillPeriod"]);
  }
  if (isset($subscription["rebillMaxIteration"])) {
    $prepareRequest->setRebillMaxIteration($subscription["rebillMaxIteration"]);
  }
}

// Transaction data
$order->setId(date("Y-m-d-H.i.s"));

if (isset($paymentMethod)) {
  $prepareRequest->setPaymentMethod($paymentMethod);
}
if (isset($operation)) {
  $prepareRequest->setOperation($operation);
}
if (isset($paymentNetwork)) {
  $prepareRequest->setPaymentNetwork($paymentNetwork);
}
$prepareRequest->setPaymentMode(PaymentMode::SINGLE);
$prepareRequest->setAmount($amount);
$prepareRequest->setCurrency($currency);
$prepareRequest->setCtrlCustomData("Give that back to me please !!");
$order->setShippingType(OrderShippingType::DIGITAL_GOODS);
$order->setDescription("Test purchase of fake product.");
$shopper->setId("1234567");
$shopper->setFirstName("John");
$shopper->setLastName("Doe");
$shopper->setAddress1("Debit Street, 45");
$shopper->setZipcode("3456TRG");
$shopper->setCity("New York");
$shopper->setState("ABC");
$shopper->setCountryCode("US");
$shopper->setHomePhonePrefix("34");
$shopper->setHomePhone("666666666");
$shopper->setEmail(isset($shopperEmailAddress) ? $shopperEmailAddress : "shopper@example.com");
$shopper->setBirthDate("19700101");
$shopper->setIdNumber("ABC-123456");
$prepareRequest->setSecure3d(isset($secure3d) ? $secure3d : false);
$account = new Account();
$account->setAge(AccountAge::DURING_TRANSACTION);
$account->setDate(date('Ymd'));
$account->setLastChange(AccountLastChange::DURING_TRANSACTION);
$account->setLastChangeDate(date('Ymd'));
$account->setPwChange(AccountPwChange::DURING_TRANSACTION);
$account->setPwChangeDate(date('Ymd'));
$account->setOrderSixMonths(1);
$account->setSuspicious(isset($secure3dNeedChallenge) ? $secure3dNeedChallenge : false);

$shopper->setAccount($account);

if (isset($callbackURL)) {
    $prepareRequest->setCtrlCallbackURL($callbackURL);
}
if (isset($redirectURL)) {
    $prepareRequest->setCtrlRedirectURL($redirectURL);
}
$shipping->setName("Lady Gogo");
$shipping->setAddress1("125 Main Street");
$shipping->setZipcode("ABC-5678");
$shipping->setState("ABC");
$shipping->setCity("New York");
$shipping->setCountryCode("US");
$shipping->setPhone("+47123456789");

if (isset($addCartProducts) && $addCartProducts) {
  $product = new CartProduct();
  $product->setCartProductId(1345)->setCartProductName("Test Product");
  $product->setCartProductUnitPrice(456)->setCartProductQuantity(1);
  $product->setCartProductBrand("Yellow Thumb")->setCartProductMPN("NA");
  $product->setCartProductCategoryName("Led screen")->setCartProductCategoryID(1234);
  $order->addCartProduct($product);

  $product = new CartProduct();
  $product->setCartProductId(6789)->setCartProductName("Test Product 2");
  $product->setCartProductUnitPrice(123)->setCartProductQuantity(1);
  $product->setCartProductBrand("Yellow Thumb")->setCartProductMPN("NA");
  $product->setCartProductCategoryName("DVD reader")->setCartProductCategoryID(1235);
  $order->addCartProduct($product);
}

if (isset($themeID)) {
  $prepareRequest->setThemeID($themeID);
}

if (isset($merchantNotification) && $merchantNotification === true) {
  $prepareRequest->setMerchantNotification(true);
  $prepareRequest->setMerchantNotificationTo($merchantNotificationTo);
  $prepareRequest->setMerchantNotificationLang($merchantNotificationLang);
}

if (isset($affiliateID)) {
  $order->setAffiliateID($affiliateID);
}
if (isset($campaignName)) {
  $order->setCampaignName($campaignName);
}

if (isset($timeOut)) {
  $prepareRequest->setTimeOut($timeOut);
}

$prepareRequest->setShopper($shopper);
$prepareRequest->setOrder($order);
$prepareRequest->setShipping($shipping);

$result = $c2pClient->preparePayment($prepareRequest);
if ($result !== false) {
  echo "Result code:" . $result->getCode() . "\n";
  echo "Result message:" . $result->getMessage() . "\n";
  echo "Get merchant status by running: php cli-payment-status.php " . $result->getMerchantToken() . "\n";
  echo "Customer access is at: " . $c2pClient->getCustomerRedirectURL($result) . "\n";
  echo "To test the decryption of status posted when the customer is redirected, use the following command:\n";
  echo "php cli-encrypted-status.php " . $result->getMerchantToken() . ' ${data_field_from_the_form}' . "\n";
} else {
  echo "Preparation error occurred: " . $c2pClient->getClientErrorMessage() . "\n";
}
