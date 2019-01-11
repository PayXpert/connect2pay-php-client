<?php
require_once (dirname(__FILE__) . "/../src/Connect2PayClient.php");
require_once (dirname(__FILE__) . "/configuration.php");
require_once (dirname(__FILE__) . "/vendor/autoload.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\WeChatDirectProcessRequest;
use WebSocket\Client;
use PayXpert\Connect2Pay\TransactionAttempt;

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);

if (isset($proxy_host) && isset($proxy_port)) {
  $c2pClient->useProxy($proxy_host, $proxy_port);
}

$amount = (isset($defaultAmount)) ? $defaultAmount : 1216;
$currency = (isset($defaultCurrency)) ? $defaultCurrency : "EUR";

$realWeChatMode = (isset($weChatDirectMode)) ? $weChatDirectMode : WeChatDirectProcessRequest::MODE_NATIVE;

// Transaction data
$c2pClient->setOrderID(date("Y-m-d-H.i.s"));

$c2pClient->setPaymentMethod(Connect2PayClient::PAYMENT_METHOD_WECHAT);
$c2pClient->setPaymentMode(Connect2PayClient::PAYMENT_MODE_SINGLE);
$c2pClient->setShopperID("1234567");
$c2pClient->setShippingType(Connect2PayClient::SHIPPING_TYPE_VIRTUAL);
$c2pClient->setAmount($amount);
$c2pClient->setOrderDescription("Test WeChat purchase.");
$c2pClient->setCurrency($currency);
$c2pClient->setShopperFirstName("John");
$c2pClient->setShopperLastName("Doe");
$c2pClient->setShopperAddress("Debit Street, 45");
$c2pClient->setShopperZipcode("3456TRG");
$c2pClient->setShopperCity("New York");
$c2pClient->setShopperState("New York");
$c2pClient->setShopperCountryCode("US");
$c2pClient->setShopperPhone("+34666666666");
$c2pClient->setShopperEmail(isset($shopperEmailAddress) ? $shopperEmailAddress : "shopper@example.com");
$c2pClient->setCtrlCustomData("Give that back to me please !!");
if (isset($redirectURL)) {
  $c2pClient->setCtrlRedirectURL($redirectURL);
}
if (isset($callbackURL)) {
  $c2pClient->setCtrlCallbackURL($callbackURL);
}

if (isset($merchantNotification) && $merchantNotification === true) {
  $c2pClient->setMerchantNotification(true);
  $c2pClient->setMerchantNotificationTo($merchantNotificationTo);
  $c2pClient->setMerchantNotificationLang($merchantNotificationLang);
}

if (isset($timeOut)) {
  $c2pClient->setTimeOut($timeOut);
}

if ($c2pClient->preparePayment()) {
  $resultCode = $c2pClient->getReturnCode();
  echo "Payment prepare returned code " . $resultCode . "\n";

  if ($resultCode == "200") {
    echo "Processing WeChat direct transaction...\n";

    $request = new WeChatDirectProcessRequest();
    $request->mode = $realWeChatMode;

    if ($request->mode == WeChatDirectProcessRequest::MODE_QUICKPAY) {
      if (isset($weChatDirectQuickPayCode)) {
        $request->setQuickPayCode($weChatDirectQuickPayCode);
      } else {
        echo "/!\ WeChat QuickPay code not defined\n";
      }
    }

    $customerToken = $c2pClient->getCustomerToken();

    $response = $c2pClient->directWeChatProcess($customerToken, $request);

    if ($response != null) {
      echo "Result code: " . $response->getCode() . "\n";
      echo "Result message: " . $response->getMessage() . "\n";
      echo "Transaction ID: " . $response->getTransactionID() . "\n";

      if ($response->getCode() == "200") {
        if ($request->mode == WeChatDirectProcessRequest::MODE_NATIVE) {
          echo "QR Code base64: " . $response->getQrCode() . "\n";
          echo "QR Code URL: " . $response->getQrCodeUrl() . "\n";
          echo "Exchange rate: " . $response->getExchangeRate() . "\n";
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
    echo "Return message is: " . $c2pClient->getReturnMessage() . "\n";
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
