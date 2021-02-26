<?php
require_once 'vendor/autoload.php';

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\Lang;
use PayXpert\Connect2Pay\containers\constant\OperationType;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\constant\PaymentNetwork;
use PayXpert\Connect2Pay\containers\constant\SubscriptionType;
use PayXpert\Connect2Pay\containers\request\WeChatDirectProcessRequest;
use PayXpert\Connect2Pay\containers\request\AliPayDirectProcessRequest;

# Configuration
$connect2pay = "https://connect2.payxpert.com/";
$originator = "000000";
$password = "gr3atPassw0rd";

// Payment methods, network and operation
//$paymentMethod = PaymentMethod::CREDITCARD;
//$paymentMethod = PaymentMethod::BANKTRANSFER;
//$paymentNetwork = PaymentNetwork::SOFORT;
//$operation = OperationType::AUTHORIZE;

// Credit Card specific field
//$secure3d = false;
//$secure3dNeedChallenge = false;

// Redirect and callback URLs
//$redirectURL = "";
//$callbackURL = "";

// Shopper notifications
$shopperEmailAddress = "shopper@example.com";

// Proxy configuration
//$proxy_host = "127.0.0.1";
//$proxy_port = 8888;
//$proxy_username = "Foo"
//$proxy_password = "Bar"

// Override the default currency
//$defaultCurrency = "USD";

// Override the default timeout of the payment
//$timeOut = "P1D";

// Subscription with predefined offer - Set $defaultAmount to the offer amount
//$subscriptionOfferId = 33;
//$defaultAmount = 2995;

// Subscription with on the fly parameters
//$subscription = array("subscriptionType" => SubscriptionType::NORMAL, "rebillAmount" => 4200, "rebillPeriod" => "P1M", "trialPeriod" => "P1M", "rebillMaxIteration" => 2);
//$subscription = array("subscriptionType" => SubscriptionType::NORMAL, "rebillAmount" => 4200, "rebillPeriod" => "P1M", "rebillMaxIteration" => 1);
//$subscription = array("subscriptionType" => SubscriptionType::INFINITE, "rebillAmount" => 4200, "rebillPeriod" => "P1M", "trialPeriod" => "P1M");
//$subscription = array("subscriptionType" => SubscriptionType::LIFETIME, "rebillAmount" => 4200);
//$subscription = array("subscriptionType" => SubscriptionType::ONETIME, "rebillPeriod" => "P1M", "rebillAmount" => 4200);

// Cart products
//$addCartProducts = true;

// Merchant notification
//$merchantNotification = true;
//$merchantNotificationTo = "sales@merchant.tld";
//$merchantNotificationLang = Lang::EN;

// Affiliation fields
//$affiliateID = 1234567;
//$campaignName = "Test Campaign";

// WeChat direct specific fields
//$weChatDirectMode = WeChatDirectProcessRequest::MODE_QUICKPAY;
//$weChatDirectQuickPayCode = "1234567890";

// AliPay direct process specific fields
// $aliPayDirectMode = AliPayDirectProcessRequest::MODE_APP;
// TO BE REPLACE WITH UPDATED BARCODE NUMBER IN SANDBOX APP
// $buyerIdentityCode = "283648183259664856";
// $identityCodeType = AliPayDirectProcessRequest::IDENTITY_CODE_TYPE_BARCODE;
