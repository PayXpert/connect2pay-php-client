<?php
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\OrderShippingType;
use PayXpert\Connect2Pay\containers\request\AliPayDirectProcessRequest;
use WebSocket\Client;
use PayXpert\Connect2Pay\containers\response\TransactionAttempt;
use PayXpert\Connect2Pay\containers\Order;
use PayXpert\Connect2Pay\containers\request\PaymentPrepareRequest;
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

$realAliPayMode = (isset($aliPayDirectMode)) ? $aliPayDirectMode : AliPayDirectProcessRequest::MODE_POS;

// Transaction data
$order->setId(date("Y-m-d-H.i.s"));

$prepareRequest->setPaymentMethod(Connect2PayClient::PAYMENT_METHOD_ALIPAY);
$prepareRequest->setPaymentMode(Connect2PayClient::PAYMENT_MODE_SINGLE);
$shopper->setId("1234567");
$order->setShippingType(OrderShippingType::DIGITAL_GOODS);
$prepareRequest->setAmount($amount);
$order->setDescription("Test AliPay purchase.");
$prepareRequest->setCurrency($currency);
$shopper->setFirstName("John");
$shopper->setLastName("Doe");
$shopper->setAddress1("Debit Street, 45");
$shopper->setZipcode("3456TRG");
$shopper->setCity("New York");
$shopper->setState("New York");
$shopper->setCountryCode("US");
$shopper->setHomePhonePrefix("34");
$shopper->setHomePhone("666666666");
$shopper->setEmail(isset($shopperEmailAddress) ? $shopperEmailAddress : "shopper@example.com");
$prepareRequest->setCtrlCustomData("Give that back to me please !!");
if (isset($redirectURL)) {
    $prepareRequest->setCtrlRedirectURL($redirectURL);
}
if (isset($callbackURL)) {
    $prepareRequest->setCtrlCallbackURL($callbackURL);
}

if (isset($merchantNotification) && $merchantNotification === true) {
    $prepareRequest->setMerchantNotification(true);
    $prepareRequest->setMerchantNotificationTo($merchantNotificationTo);
    $prepareRequest->setMerchantNotificationLang($merchantNotificationLang);
}

if (isset($timeOut)) {
    $prepareRequest->setTimeOut($timeOut);
}

$prepareRequest->setShopper($shopper);
$prepareRequest->setOrder($order);

$result = $c2pClient->preparePayment($prepareRequest);
if ($result !== false) {
  $resultCode = $result->getCode();
  echo "Payment prepare returned code " . $resultCode . "\n";

  if ($resultCode == "200") {
    echo "Processing AliPay direct transaction...\n";

    $request = new AliPayDirectProcessRequest();
    $request->setMode($realAliPayMode);

    if ($request->getMode() == AliPayDirectProcessRequest::MODE_APP) {
      if (isset($buyerIdentityCode) && isset($identityCodeType)) {
        $request->setBuyerIdentityCode($buyerIdentityCode);
        $request->setIdentityCodeType($identityCodeType);
      } else {
        echo "/!\ AliPay APP code not defined\n";
      }
    }

    $customerToken = $c2pClient->getCustomerToken();

    $response = $c2pClient->directAliPayProcess($customerToken, $request);

    if ($response != null) {
      echo "Result code: " . $response->getCode() . "\n";
      echo "Result message: " . $response->getMessage() . "\n";
      echo "Transaction ID: " . $response->getTransactionID() . "\n";

      if ($response->getCode() == "200") {
        if ($request->getMode() == AliPayDirectProcessRequest::MODE_POS) {
          echo "QR Code base64: " . $response->getQrCode() . "\n";
          echo "QR Code URL: " . $response->getQrCodeUrl() . "\n";
          echo "Exchange rate: " . $response->getExchangeRate() . "\n";
        } elseif ($request->getMode() == AliPayDirectProcessRequest::MODE_SDK) {
          echo "Raw SDK Request: " . $response->getRawRequest() . "\n";
        } else {
          printTransaction($response->getTransactionInfo());
        }

        if ($response->getWebSocketUrl()) {
          echo "Web Socket URL: " . $response->getWebSocketUrl() . "\n";
          echo "Listening on WebSocket...\n";

          // Listen to the WebSocket
          $client = new Client($response->getWebSocketUrl(), array("timeout" => 240));

          try {
            $transactionJson = $client->receive();

            if ($transactionJson != null && strlen(trim($transactionJson)) > 0) {
              echo "Received transaction status:\n";

              printTransaction(TransactionAttempt::getFromRawJson($transactionJson));
            }
          } catch (Exception $e) {
            echo "Error receiving information from the WebSocket: " . $e->getMessage() . "\n";
          }
        }
      }
    } else {
      echo "No response received. Terminating\n";
    }
  } else {
    echo "Return message is: " . $result->getMessage() . "\n";
    echo "Terminating\n";
  }
} else {
  echo "Preparation error occurred: " . $c2pClient->getClientErrorMessage() . "\n";
}

function printTransaction($transaction) {
  if ($transaction != null) {
    echo "Provider transaction ID: " . $transaction->getProviderTransactionID() . "\n";
    echo "Payment method: " . $transaction->getPaymentMethod() . "\n";
    echo "Operation: " . $transaction->getOperation() . "\n";
    echo "Amount: " . number_format($transaction->getAmount() / 100, 2) . " " . $transaction->getCurrency() . "\n";
    echo "Result code: " . $transaction->getResultCode() . "\n";
    echo "Result message: " . $transaction->getResultMessage() . "\n";

    $transactionDate = $transaction->getDateAsDateTime();
    if ($transactionDate !== null) {
      echo "Transaction date: " . $transactionDate->format("Y-m-d H:i:s T") . "\n";
    }
  }
}
