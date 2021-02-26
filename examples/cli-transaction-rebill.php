<?php
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

// TransactionId should be passed as the first parameter of this script
// Amount in cents should be passed as the second parameter of this script
if ($argc < 3) {
  echo "Usage: php cli-transaction-rebill.php transactionID amount\n";
  exit(1);
}

$transactionId = $argv[1];
$amount = $argv[2];

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);
$status = $c2pClient->rebillTransaction($transactionId, $amount);

if ($status != null && $status->getCode() != null) {
  $code = (int) $status->getCode();
  echo "Rebill result:\n";
  echo "~ Error code: " . $status->getCode() . "\n";
  echo "~ Error message: " . $status->getMessage() . "\n";
  echo "~ Transaction ID: " . $status->getTransactionID() . "\n";
  echo "~ Operation: " . $status->getOperation() . "\n";
} else {
  echo "Error: " . $c2pClient->getClientErrorMessage() . "\n";
}
?>
