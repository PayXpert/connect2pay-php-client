<?php
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;

// Merchant token should be passed as the first parameter of this script
// Encrypted status should be passed as the second argument
if ($argc < 3) {
  echo "Usage: php encrypted-status.php merchantToken encryptedstatus\n";
  exit(1);
}

$merchantToken = $argv[1];
$encryptedStatus = $argv[2];

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);
if ($c2pClient->handleRedirectStatus($encryptedStatus, $merchantToken)) {
  $status = $c2pClient->getStatus();
  if ($status != null && $status->getErrorCode() != null) {
    echo "Merchant token: " . $status->getMerchantToken() . "\n";
    echo "Status: " . $status->getStatus() . "\n";
    echo "Error code: " . $status->getErrorCode() . "\n";

    $transaction = $status->getLastInitialTransactionAttempt();

    if ($transaction !== null) {
      echo "Number of transaction attempts: " . count($status->getTransactions()) . "\n";
      echo "Payment Method: " . $transaction->getPaymentMethod() . "\n";
      if ($transaction->getPaymentNetwork()) {
        echo "Payment network: " . $transaction->getPaymentNetwork() . "\n";
      }
      echo "Operation: " . $transaction->getOperation() . "\n";

      echo "Error message: " . $transaction->getResultMessage() . "\n";
      echo "Transaction ID: " . $transaction->getTransactionID() . "\n";

      $transactionDate = $transaction->getDateAsDateTime();
      if ($transactionDate !== null) {
        echo "Transaction date: " . $transactionDate->format("Y-m-d H:i:s T") . "\n";
      }

      if ($transaction->getSubscriptionID()) {
        echo "Subscription ID: " . $transaction->getSubscriptionID() . "\n";
      }
      $paymentMeanInfo = $transaction->getPaymentMeanInfo();
      if ($paymentMeanInfo !== null) {
        switch ($transaction->getPaymentMethod()) {
          case PaymentMethod::CREDIT_CARD:
            if ($paymentMeanInfo->getCardNumber() !== null) {
              echo "Payment Mean Information:\n";
              echo "* Card Holder Name: " . $paymentMeanInfo->getCardHolderName() . "\n";
              echo "* Card Number: " . $paymentMeanInfo->getCardNumber() . "\n";
              echo "* Card Expiration: " . $paymentMeanInfo->getCardExpireMonth() . "/" . $paymentMeanInfo->getCardExpireYear() . "\n";
              echo "* Card Brand: " . $paymentMeanInfo->getCardBrand() . "\n";
              if ($paymentMeanInfo->getCardLevel() !== null) {
                echo "* Card Level/subtype: " . $paymentMeanInfo->getCardLevel() . "/" . $paymentMeanInfo->getCardSubType() . "\n";
                echo "* Card country code: " . $paymentMeanInfo->getIinCountry() . "\n";
                echo "* Card bank name: " . $paymentMeanInfo->getIinBankName() . "\n";
              }
            }
            break;
          case PaymentMethod::BANK_TRANSFER:
            $sender = $paymentMeanInfo->getSender();
            $recipient = $paymentMeanInfo->getRecipient();
            if ($sender !== null) {
              echo "Payment Mean Information:\n";
              echo "* Sender Account:\n";
              echo ">> Holder Name: " . $sender->getHolderName() . "\n";
              echo ">> Bank Name: " . $sender->getBankName() . "\n";
              echo ">> IBAN: " . $sender->getIban() . "\n";
              echo ">> BIC: " . $sender->getBic() . "\n";
              echo ">> Country code: " . $sender->getCountryCode() . "\n";
            }
            if ($recipient !== null) {
              echo "* Recipient Account:\n";
              echo ">> Holder Name: " . $recipient->getHolderName() . "\n";
              echo ">> Bank Name: " . $recipient->getBankName() . "\n";
              echo ">> IBAN: " . $recipient->getIban() . "\n";
              echo ">> BIC: " . $recipient->getBic() . "\n";
              echo ">> Country code: " . $recipient->getCountryCode() . "\n";
            }
            break;
          case PaymentMethod::DIRECT_DEBIT:
            $account = $paymentMeanInfo->getBankAccount();

            if ($account !== null) {
              echo "Payment Mean Information:\n";
              echo "* Statement descriptor: " . $paymentMeanInfo->getStatementDescriptor() . "\n";

              $collectedAt = $paymentMeanInfo->getCollectedAtAsDateTime();
              if ($collectedAt != null) {
                echo "* Collected At: " . $collectedAt->format("Y-m-d H:i:s T") . "\n";
              }

              echo "* Bank Account:\n";
              echo ">> Holder Name: " . $account->getHolderName() . "\n";
              echo ">> Bank Name: " . $account->getBankName() . "\n";
              echo ">> IBAN: " . $account->getIban() . "\n";
              echo ">> BIC: " . $account->getBic() . "\n";
              echo ">> Country code: " . $account->getCountryCode() . "\n";

              $sepaMandate = $account->getSepaMandate();

              if ($sepaMandate != null) {
                echo "* SEPA mandate:\n";
                echo ">> Description: " . $sepaMandate->getDescription() . "\n";
                echo ">> Status: " . $sepaMandate->getStatus() . "\n";
                echo ">> Type: " . $sepaMandate->getType() . "\n";
                echo ">> Scheme: " . $sepaMandate->getScheme() . "\n";
                echo ">> Signature type: " . $sepaMandate->getSignatureType() . "\n";
                echo ">> Phone number: " . $sepaMandate->getPhoneNumber() . "\n";

                $signedAt = $sepaMandate->getSignedAtAsDateTime();
                if ($signedAt != null) {
                  echo ">> Signed at: " . $signedAt->format("Y-m-d H:i:s T") . "\n";
                }

                $createdAt = $sepaMandate->getSignedAtAsDateTime();
                if ($createdAt != null) {
                  echo ">> Created at: " . $createdAt->format("Y-m-d H:i:s T") . "\n";
                }

                $lastUsedAt = $sepaMandate->getLastUsedAtAsDateTime();
                if ($lastUsedAt != null) {
                  echo ">> Last used at: " . $lastUsedAt->format("Y-m-d H:i:s T") . "\n";
                }

                echo ">> Download URL: " . $sepaMandate->getDownloadUrl() . "\n";
              }
            }

            break;
        }
      }
      if ($status->getCtrlCustomData()) {
        echo "Custom Data: " . $status->getCtrlCustomData() . "\n";
      }
      $shopper = $transaction->getShopper();
      if ($shopper !== null) {
        echo "Shopper info:\n";
        echo "* Name: " . $shopper->getFirstName() . " " . $shopper->getLastName() . "\n";
        echo "* Address: " . $shopper->getAddress1() . " - " . $shopper->getZipcode() . " " . $shopper->getCity() . " - " .
             $shopper->getCountryCode() . "\n";
        echo "* Email: " . $shopper->getEmail() . "\n";
        if ($shopper->getBirthDate() !== null) {
          echo "* Birth date: " . $shopper->getBirthDate() . "\n";
        }
        if ($shopper->getIdNumber() !== null) {
          echo "* ID Number: " . $shopper->getIdNumber() . "\n";
        }
        if ($shopper->getIpAddress() !== null) {
          echo "* IP Address: " . $shopper->getIpAddress() . "\n";
        }
      }
    }
  }
} else {
  echo "Error: " . $c2pClient->getClientErrorMessage() . "\n";
}
?>
