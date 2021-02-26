<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\AccountAge;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\response\AlipayPaymentMeanInfo;
use PayXpert\Connect2Pay\containers\response\DirectDebitPaymentMeanInfo;
use PayXpert\Connect2Pay\containers\response\PaymentStatus;
use PayXpert\Connect2Pay\containers\response\WeChatDirectProcessResponse;
use PayXpert\Connect2Pay\containers\response\WeChatPaymentMeanInfo;

/**
 * @covers PaymentStatus
 */
final class PaymentStatusTest extends CommonTest
{

    public function testInitFromJson()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(1050, $paymentStatus->getAmount());
        $this->assertEquals("submission", $paymentStatus->getOperation());
        $this->assertEquals("000", $paymentStatus->getErrorCode());
        $this->assertEquals("Transaction successfully completed", $paymentStatus->getErrorMessage());
        $this->assertEquals("2018-01-31-16.07.34", $paymentStatus->getOrder()->getId());
    }

    public function testGetLastInitialTransactionAttempt()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(6, count($paymentStatus->getTransactions()));

        $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

        $this->assertNotNull($lastInitialTransaction);
        $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());
        $this->assertEquals(PaymentMethod::DIRECT_DEBIT, $lastInitialTransaction->getPaymentMethod());
        $this->assertEquals("submission", $lastInitialTransaction->getOperation());

        $paymentMeanInfo = $lastInitialTransaction->getPaymentMeanInfo();

        $this->assertNotNull($paymentMeanInfo);
        $this->assertTrue($paymentMeanInfo instanceof DirectDebitPaymentMeanInfo);
        $this->assertEquals("PayXpert Direct Debit Service", $paymentMeanInfo->getStatementDescriptor());

        $bankAccount = $paymentMeanInfo->getBankAccount();
        $this->assertNotNull($bankAccount);
        $this->assertEquals("NL32RABO019490XXXX", $bankAccount->getIban());
        $this->assertEquals("RABONL2UXXX", $bankAccount->getBic());
        $this->assertEquals("RABOBANK NEDERLAND", $bankAccount->getBankName());
        $this->assertEquals("John Doe", $bankAccount->getHolderName());
        $this->assertEquals("NL", $bankAccount->getCountryCode());

        $sepaMandate = $bankAccount->getSepaMandate();
        $this->assertNotNull($sepaMandate);
        $this->assertEquals("Firmado fuera de BeSEPA.", $sepaMandate->getDescription());
        $this->assertEquals("SIGNED", $sepaMandate->getStatus());
    }

    public function testGetReferringTransactionAttempts()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(6, count($paymentStatus->getTransactions()));

        // Get the last initial transaction attempt (direct debit submission)
        $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

        $this->assertNotNull($lastInitialTransaction);
        $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());

        // Get the cancel transactions referring to the initial submission (there is one)
        $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID(), "cancel");
        $this->assertEquals(1, count($referringTransactions));
        $this->assertEquals("cancel", $referringTransactions[0]->getOperation());
        $this->assertEquals("1v0masio6c-iwee", $referringTransactions[0]->getTransactionID());

        // Get the collection transactions referring to the initial submission (there is none)
        $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID(), "collection");
        $this->assertEquals(0, count($referringTransactions));

        // Get all the transactions referring to the initial submission
        $referringTransactions = $paymentStatus->getReferringTransactionAttempts($lastInitialTransaction->getTransactionID());
        $this->assertEquals(4, count($referringTransactions));

        // First transaction is a new submission (rebill)
        $rebill = $referringTransactions[0];
        $this->assertNotNull($rebill);
        $this->assertEquals("1v0masio6c-naxo", $rebill->getTransactionID());
        $this->assertEquals("submission", $rebill->getOperation());

        // Get collection transaction referring to the submission
        $referringTransactions = $paymentStatus->getReferringTransactionAttempts($rebill->getTransactionID(), "collection");
        $this->assertEquals(1, count($referringTransactions));

        $collection = $referringTransactions[0];
        $this->assertEquals("1v0masio6c-e1ir", $collection->getTransactionID());
    }

    public function testGetReferringTransactionAttempt()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(6, count($paymentStatus->getTransactions()));

        // Get the last initial transaction attempt (direct debit submission)
        $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

        $this->assertNotNull($lastInitialTransaction);
        $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());

        // Get the cancel transaction referring to the initial submission
        $referringTransaction = $paymentStatus->getReferringTransactionAttempt($lastInitialTransaction->getTransactionID(), "cancel");
        $this->assertNotNull($referringTransaction);
        $this->assertEquals("cancel", $referringTransaction->getOperation());
        $this->assertEquals("1v0masio6c-iwee", $referringTransaction->getTransactionId());

        // Get a submission (rebill) referring to the initial submission
        $referringTransaction = $paymentStatus->getReferringTransactionAttempt($lastInitialTransaction->getTransactionID(), "submission");
        $this->assertNotNull($referringTransaction);
        $this->assertEquals("submission", $referringTransaction->getOperation());
        $this->assertEquals("1v0masio6c-naxo", $referringTransaction->getTransactionId());

        // Get a collection referring to the last submission
        $collection = $paymentStatus->getReferringTransactionAttempt($referringTransaction->getTransactionID(), "collection");
        $this->assertNotNull($collection);
        $this->assertEquals("collection", $collection->getOperation());
        $this->assertEquals("1v0masio6c-e1ir", $collection->getTransactionId());
    }

    public function testTransactionAttemptWeChat()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getWeChatPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(1, count($paymentStatus->getTransactions()));

        // Get the last initial transaction attempt (WeChat sale)
        $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

        $this->assertNotNull($lastInitialTransaction);
        $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());

        $paymentMeanInfo = $lastInitialTransaction->getPaymentMeanInfo();

        $this->assertNotNull($paymentMeanInfo);
        $this->assertTrue($paymentMeanInfo instanceof WeChatPaymentMeanInfo);
        $this->assertEquals(568.90, $paymentMeanInfo->getTotalFee());
        $this->assertEquals(7.8945, $paymentMeanInfo->getExchangeRate());
    }

    public function testTransactionAttemptAlipay()
    {
        $paymentStatus = PaymentStatus::getFromJson(Connect2PayAPIMock::getAlipayPaymentStatusMock());

        $this->assertNotNull($paymentStatus);
        $this->assertEquals(1, count($paymentStatus->getTransactions()));

        // Get the last initial transaction attempt (Alipay sale)
        $lastInitialTransaction = $paymentStatus->getLastInitialTransactionAttempt();

        $this->assertNotNull($lastInitialTransaction);
        $this->assertEquals("1v0masio6c-1nrr", $lastInitialTransaction->getTransactionID());

        $paymentMeanInfo = $lastInitialTransaction->getPaymentMeanInfo();

        $this->assertNotNull($paymentMeanInfo);
        $this->assertTrue($paymentMeanInfo instanceof AlipayPaymentMeanInfo);
        $this->assertEquals(568.90, $paymentMeanInfo->getTotalFee());
        $this->assertEquals(7.8945, $paymentMeanInfo->getExchangeRate());
    }
}
