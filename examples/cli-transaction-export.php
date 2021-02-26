<?php
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\request\ExportTransactionsRequest;

$secondsPerDay = 86400;

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

$request = new ExportTransactionsRequest();

// fetch for October 2019 (in current timezone)
$request->setStartTime(mktime(0, 0, 0, 10, 1, 2019));
$request->setEndTime(mktime(0, 0, 0, 11, 1, 2019));

// Set additional filtering if required:
// $request->setOperation(...)
// $request->setPaymentMethod(...)
// $request->setTransactionResultCode(...)
// $request->setPaymentNetwork(...)

$response = $c2pClient->exportTransactions($request);

if ($response != null) {
  $sum = 0;
  foreach ($response->getTransactions() as $transaction) {
    $id = $transaction->getTransactionID();
    $amount = $transaction->getAmount();
    $transactionDate = $transaction->getDateAsDateTime();
    if ($transactionDate !== null) {
      $transactionDate = $transactionDate->format("Y-m-d H:i:s T");
      echo $transactionDate.": Transaction ".$id." with amount ".$amount."\n";
      $sum = $sum + $amount;
    }
  }
  echo "Overall transaction amount: ".$sum."\n";
} else {
  echo "Error: " . $c2pClient->getClientErrorMessage() . "\n";
}