<?php
/*
 * This file shows how to cancel a subscription using the connect2pay client
 */
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\SubscriptionCancelReason;

// Subscription ID should be passed as the first parameter of this script
if ($argc < 2) {
  echo "Usage: php cancel-subscription.php subscriptionID\n";
  exit(1);
}

$subscriptionID = $argv[1];

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

if (isset($proxy_host) && isset($proxy_port)) {
  $c2pClient->useProxy($proxy_host, $proxy_port);
}

$result = $c2pClient->cancelSubscription($subscriptionID, SubscriptionCancelReason::BANK_DENIAL);

if ($result == "200") {
  echo "Subscription " . $subscriptionID . " cancelled successfully: " . $c2pClient->getClientErrorMessage() . "\n";
} else {
  echo "Error canceling subscription " . $subscriptionID . ": code " . $result . " with message: " . $c2pClient->getClientErrorMessage() . "\n";
}
