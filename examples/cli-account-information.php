<?php
require_once (dirname(__FILE__) . "/../src/Connect2PayClient.php");
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);
$info = $c2pClient->getAccountInformation();

if ($info != null) {
  echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
  echo "~~~ API Account information ~~~\n";
  echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
  echo "Account name: " . $info->getName() . "\n";
  echo "Display Terms and conditions: " . ($info->getDisplayTerms() ? "yes" : "no") . "\n";
  echo "Terms and conditions URL: " . $info->getTermsUrl() . "\n";
  echo "Customer support URL: " . $info->getSupportUrl() . "\n";
  echo "Number of payment attempts allowed: " . $info->getMaxAttempts() . "\n";
  echo "Shopper Email notification on success: " . ($info->getNotificationOnSuccess() ? "yes" : "no") . "\n";
  echo "Shopper Email notification on failure: " . ($info->getNotificationOnFailure() ? "yes" : "no") . "\n";
  echo "Shopper Email notification sender: " . $info->getNotificationSenderName() . " <" . $info->getNotificationSenderEmail() . ">\n";
  echo "Merchant Email notification: " . ($info->getMerchantNotification() ? "yes" : "no") . "\n";
  echo "Merchant Email notification recipient: " . $info->getMerchantNotificationTo() . "\n";
  echo "Merchant Email notification language: " . $info->getMerchantNotificationLang() . "\n";

  if ($info->getPaymentMethods() !== null) {
    echo "Enabled Payment Methods information:\n";

    foreach ($info->getPaymentMethods() as $methodInfo) {
      echo "~~ Payment Method: " . $methodInfo->getPaymentMethod();
      if ($methodInfo->getPaymentNetwork() != null) {
        echo " via " . $methodInfo->getPaymentNetwork() . " network";
      }
      echo "\n";

      echo "** Currencies: " . implode(', ', $methodInfo->getCurrencies()) . "\n";
      echo "** Default operation: " . $methodInfo->getDefaultOperation() . "\n";

      if ($methodInfo->getOptions() !== null) {
        echo "** Payment Method options:\n";

        foreach ($methodInfo->getOptions() as $methodOption) {
          echo "*** " . $methodOption->getName() . " => " . $methodOption->getValue() . "\n";
        }
      }
    }
  }
} else {
  echo "Error: " . $c2pClient->getClientErrorMessage() . "\n";
}
