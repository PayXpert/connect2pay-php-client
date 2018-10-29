<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\DirectDebitPaymentMeanInfo;
use PayXpert\Connect2Pay\PaymentStatus;

/**
 * @covers PaymentStatus
 */
final class PaymentStatusTest extends CommonTest {

  public function testInitFromJson() {
    $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

    $this->assertNotNull($paymentStatus);
    $this->assertEquals(3999, $paymentStatus->getAmount());
    $this->assertEquals("submission", $paymentStatus->getOperation());
    $this->assertEquals("000", $paymentStatus->getErrorCode());
    $this->assertEquals("Transaction successfully completed", $paymentStatus->getErrorMessage());
    $this->assertEquals("1234567D1", $paymentStatus->getOrderID());
  }

  public function testGetLastInitialTransactionAttempt() {
    $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

    $this->assertNotNull($paymentStatus);
    $this->assertEquals(5, count($paymentStatus->getTransactions()));

    $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

    $this->assertNotNull($lastInitialTransaction);
    $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());
    $this->assertEquals(Connect2PayClient::PAYMENT_METHOD_DIRECTDEBIT, $lastInitialTransaction->getPaymentMethod());
    $this->assertEquals("submission", $lastInitialTransaction->getOperation());

    $paymentMeanInfo = $lastInitialTransaction->getPaymentMeanInfo();

    $this->assertNotNull($paymentMeanInfo);
    $this->assertTrue($paymentMeanInfo instanceof DirectDebitPaymentMeanInfo);
    $this->assertEquals("Test statement descriptor", $paymentMeanInfo->getStatementDescriptor());

    $bankAccount = $paymentMeanInfo->getBankAccount();
    $this->assertNotNull($bankAccount);
    $this->assertEquals("NL29ABNA050988XXXX", $bankAccount->getIban());
    $this->assertEquals("ABNANL2AXXX", $bankAccount->getBic());
    $this->assertEquals("BNP", $bankAccount->getBankName());
    $this->assertEquals("John Snow", $bankAccount->getHolderName());
    $this->assertEquals("NL", $bankAccount->getCountryCode());

    $sepaMandate = $bankAccount->getSepaMandate();
    $this->assertNotNull($sepaMandate);
    $this->assertEquals("Unit Test Mandate", $sepaMandate->getDescription());
    $this->assertEquals("SIGNED", $sepaMandate->getStatus());
  }

  public function testGetReferringTransactionAttempts() {
    $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

    $this->assertNotNull($paymentStatus);
    $this->assertEquals(5, count($paymentStatus->getTransactions()));

    // Get the last initial transaction attempt (direct debit submission)
    $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

    $this->assertNotNull($lastInitialTransaction);

    // Get the cancel transactions referring to the initial submission (there is one)
    $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID(), "cancel");
    $this->assertEquals(1, count($referringTransactions));
    $this->assertEquals("cancel", $referringTransactions[0]->getOperation());

    // Get the collection transactions referring to the initial submission (there is none)
    $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID(), "collection");
    $this->assertEquals(0, count($referringTransactions));

    // Get all the transactions referring to the initial submission
    $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID());
    $this->assertEquals(2, count($referringTransactions));

    // Second transaction is a new submission (rebill)
    $rebill = $referringTransactions[1];
    $this->assertNotNull($rebill);
    $this->assertEquals("submission", $rebill->getOperation());

    // Get collection transaction referring to the submission
    $referringTransactions = $paymentStatus->getReferringTransactionAttempts($rebill->getTransactionID(), "collection");
    $this->assertEquals(1, count($referringTransactions));

    $collection = $referringTransactions[0];

    // Get transactions referring to the collection (one refund request)
    $referringTransactions = $paymentStatus->getReferringTransactionAttempts($collection->getTransactionID());
    $this->assertEquals(1, count($referringTransactions));
    $this->assertEquals("refund_request", $referringTransactions[0]->getOperation());
  }

  public function testGetReferringTransactionAttempt() {
    $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

    $this->assertNotNull($paymentStatus);
    $this->assertEquals(5, count($paymentStatus->getTransactions()));

    // Get the last initial transaction attempt (direct debit submission)
    $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

    $this->assertNotNull($lastInitialTransaction);

    // Get the cancel transaction referring to the initial submission
    $referringTransaction = $paymentStatus->getReferringTransactionAttempt($lastInitialTransaction->getTransactionID(), "cancel");
    $this->assertNotNull($referringTransaction);
    $this->assertEquals("cancel", $referringTransaction->getOperation());
    $this->assertEquals("1v0masio6c-xdfv", $referringTransaction->getTransactionId());

    // Get a submission (rebill) referring to the initial submission
    $referringTransaction = $paymentStatus->getReferringTransactionAttempt($lastInitialTransaction->getTransactionID(), "submission");
    $this->assertNotNull($referringTransaction);
    $this->assertEquals("submission", $referringTransaction->getOperation());
    $this->assertEquals("1v0masio6c-k3ln", $referringTransaction->getTransactionId());

    // Get a collection referring to the last submission
    $collection = $paymentStatus->getReferringTransactionAttempt($referringTransaction->getTransactionID(), "collection");
    $this->assertNotNull($collection);
    $this->assertEquals("collection", $collection->getOperation());
    $this->assertEquals("1v0masio6c-xc3g", $collection->getTransactionId());
  }
}
