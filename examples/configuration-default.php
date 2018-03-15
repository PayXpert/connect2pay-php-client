<?php
use PayXpert\Connect2Pay\Connect2PayClient;

# Configuration
$connect2pay = "https://connect2.payxpert.com/";
$originator = "000000";
$password = "gr3atPassw0rd";

// Payment types, operation and Provider
//$paymentType = Connect2PayClient::_PAYMENT_TYPE_CREDITCARD;
//$paymentType = Connect2PayClient::_PAYMENT_TYPE_BANKTRANSFER;
//$provider = Connect2PayClient::_PAYMENT_PROVIDER_SOFORT;
//$operation = Connect2PayClient::_OPERATION_TYPE_AUTHORIZE;

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
//$merchantNotificationLang = Connect2PayClient::_LANG_EN;

// Affiliation fields
//$affiliateID = 1234567;
//$campaignName = "Test Campaign";