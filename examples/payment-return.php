<?php
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

session_start();
// We restore from the session the token info
$merchantToken = $_SESSION['merchantToken'];

if ($merchantToken != null) {
  // Extract data received from the payment page
  $data = $_POST["data"];

  if ($data != null) {
    // Setup the client and decrypt the redirect Status
    $c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

    if ($c2pClient->handleRedirectStatus($data, $merchantToken)) {
      // Get the PaymentStatus object
      $status = $c2pClient->getStatus();

      $errorCode = $status->getErrorCode();
      $merchantData = $status->getCtrlCustomData();

      // errorCode = 000 => payment is successful
      if ($errorCode == '000') {
        // Display the payment confirmation page
      } else {
        // Display the payment error page
      }
    }
  }
}

// If here, display the error page
// ...