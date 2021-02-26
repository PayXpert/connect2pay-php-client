<?php
/*
 * This file shows how to handle the payment callback issued by the payment page
 * system. This script must be hosted under the URL provided during the payment
 * creation in the ctrlCallbackURL parameter
 */
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

// Setup the connection and handle Callback Status
$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);
if ($c2pClient->handleCallbackStatus()) {
  // Get the PaymentStatus object
  $status = $c2pClient->getStatus();

  // The payment status code
  $errorCode = $status->getErrorCode();
  // Custom data that could have been provided in ctrlCustomData when creating
  // the payment
  $merchantData = $status->getCtrlCustomData();
  // The unique token, known only by the payment page and the merchant
  $merchantToken = $status->getMerchantToken();

  // Get the last transaction processed for this payment
  $transaction = $status->getLastInitialTransactionAttempt();

  $transactionId = null;
  if ($transaction !== null) {
    // The transaction ID generated for this payment
    $transactionId = $transaction->getTransactionID();
  }

  // /!\ /!\
  // The received callback *must* be authenticated by checking that the merchant
  // token matches with a previous known transaction. If this check is not done,
  // anyone can manipulate the payment status by providing fake data to this
  // script.
  // For example the merchant token can be stored with the application order
  // system (ctrlCustomData could also be used to authenticate in other ways)
  $order = Order::findFromMerchantToken($merchantToken);

  if ($order != null) {
    // errorCode = 000 => payment transaction is successful
    if ($errorCode == '000') {
      // If we reach this part of the code the payment succeeded
      // Do the required stuff to validate the payment in the application
    } else {
      // Add here the code in case the payment is denied
    }

    // Some debug statement example
    $log = "Received a new transaction status from " . $_SERVER["REMOTE_ADDR"] . ". Merchant token: " . $merchantToken . ", Status: " .
         $status->getStatus() . ", Error code: " . $errorCode;
    if ($errorCode >= 0) {
      $log .= ", Error message: " . $status->getErrorMessage();
      $log .= ", Transaction ID: " . $transactionId;
    }
    syslog(LOG_INFO, $log);

    // Send back a response to mark this transaction as notified on the payment
    // page
    $response = array("status" => "OK", "message" => "Status recorded");
    header("Content-type: application/json");
    echo json_encode($response);
  } else {
    syslog(LOG_ERR, "Error. No order found for token " . $merchantToken . " in callback from " . $_SERVER["REMOTE_ADDR"] . ".");
  }
} else {
  syslog(LOG_ERR, "Error. Received an incorrect status from " . $_SERVER["REMOTE_ADDR"] . ".");
}

// Send back a default error response
$response = array("status" => "KO", "message" => "Error handling the callback");
header("Content-type: application/json");
echo json_encode($response);