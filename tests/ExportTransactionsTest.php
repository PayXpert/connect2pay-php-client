<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\request\ExportTransactionsRequest;

/**
 * @covers Connect2PayClient.exportTransactions
 */
final class ExportTransactionsTest extends CommonTest {

  public function testExportTransactions() {
    $request = new ExportTransactionsRequest();
    $request->setStartTime(mktime(0, 0, 0, 1, 1, 2019));
    $request->setEndTime(mktime(0, 0, 0, 6, 1, 2019));

    $response = $this->c2pClient->exportTransactions($request);

    $this->assertNotNull($response);
    $this->assertFalse(empty($response->getTransactions()));
  }

  public function testExportTransactionsFilterResultCode() {
    $request = new ExportTransactionsRequest();
    $request->setStartTime(mktime(0, 0, 0, 1, 1, 2019));
    $request->setEndTime(mktime(0, 0, 0, 6, 1, 2019));
    $request->setTransactionResultCode(0);

    $response = $this->c2pClient->exportTransactions($request);

    $this->assertNotNull($response);
    $this->assertFalse(empty($response->getTransactions()));
    foreach ($response->getTransactions() as $transaction) {
      $this->assertEquals("000", $transaction->getResultCode());
    }
  }

  public function testExportTransactionsFilterOperation() {
    $request = new ExportTransactionsRequest();
    $request->setStartTime(mktime(0, 0, 0, 1, 1, 2019));
    $request->setEndTime(mktime(0, 0, 0, 6, 1, 2019));
    $request->setOperation("authorize");

    $response = $this->c2pClient->exportTransactions($request);

    $this->assertNotNull($response);
    $this->assertFalse(empty($response->getTransactions()));
    foreach ($response->getTransactions() as $transaction) {
      $this->assertEquals("authorize", $transaction->getOperation());
    }
  }

  public function testExportTransactionsFilterPaymentMethod() {
    $request = new ExportTransactionsRequest();
    $request->setStartTime(mktime(0, 0, 0, 1, 1, 2019));
    $request->setEndTime(mktime(0, 0, 0, 6, 1, 2019));
    $request->setPaymentMethod("CreditCard");

    $response = $this->c2pClient->exportTransactions($request);

    $this->assertNotNull($response);
    $this->assertFalse(empty($response->getTransactions()));
    foreach ($response->getTransactions() as $transaction) {
      $this->assertEquals("CreditCard", $transaction->getPaymentMethod());
    }
  }

  public function testExportTransactionsFilterPaymentNetwork() {
    $request = new ExportTransactionsRequest();
    $request->setStartTime(mktime(0, 0, 0, 1, 1, 2018));
    $request->setEndTime(mktime(0, 0, 0, 1, 1, 2019));
    $request->setPaymentMethod("BankTransfer");
    $request->setPaymentNetwork("GiroPay");

    $response = $this->c2pClient->exportTransactions($request);

    $this->assertNotNull($response);
    $this->assertFalse(empty($response->getTransactions()));
    foreach ($response->getTransactions() as $transaction) {
      $this->assertEquals("giropay", $transaction->getPaymentNetwork());
    }
  }
}
