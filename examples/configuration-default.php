<?php
use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\WeChatDirectProcessRequest;
use PayXpert\Connect2Pay\AliPayDirectProcessRequest;

# Configuration
$connect2pay = "https://connect2.payxpert.com/";
$originator = "000000";
$password = "gr3atPassw0rd";

// Payment methods, network and operation
//$paymentMethod = Connect2PayClient::PAYMENT_METHOD_CREDITCARD;
//$paymentMethod = Connect2PayClient::PAYMENT_METHOD_BANKTRANSFER;
//$paymentNetwork = Connect2PayClient::PAYMENT_NETWORK_SOFORT;
//$operation = Connect2PayClient::OPERATION_TYPE_AUTHORIZE;

// Credit Card specific field
//$secure3d = false;

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
//$subscription = array("subscriptionType" => "normal", "rebillAmount" => 4200, "rebillPeriod" => "P1M", "trialPeriod" => "P1M", "rebillMaxIteration" => 2);
//$subscription = array("subscriptionType" => "normal", "rebillAmount" => 4200, "rebillPeriod" => "P1M", "rebillMaxIteration" => 1);
//$subscription = array("subscriptionType" => "infinite", "rebillAmount" => 4200, "rebillPeriod" => "P1M", "trialPeriod" => "P1M");
//$subscription = array("subscriptionType" => "lifetime", "rebillAmount" => 4200);
//$subscription = array("subscriptionType" => "onetime", "rebillPeriod" => "P1M", "rebillAmount" => 4200);

// Cart products
//$addCartProducts = true;

// Merchant notification
//$merchantNotification = true;
//$merchantNotificationTo = "sales@merchant.tld";
//$merchantNotificationLang = Connect2PayClient::LANG_EN;

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
