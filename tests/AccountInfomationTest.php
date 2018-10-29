<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\AccountInformation;
use PayXpert\Connect2Pay\Connect2PayClient;

/**
 *
 * @covers AccountInformation
 */
final class AccountInformationTest extends CommonTest {

  public function testGetFromJson() {
    $accountInformation = AccountInformation::getFromJson(Connect2PayAPIMock::getAccountInformationMock());

    $this->assertNotNull($accountInformation);
    $this->assertEquals("002.60", $accountInformation->getApiVersion());
    $this->assertEquals("Unit Test Website", $accountInformation->getName());
    $this->assertEquals(false, $accountInformation->getDisplayTerms());
    $this->assertEquals("", $accountInformation->getTermsUrl());
    $this->assertEquals("https://www.payxpert.com/support", $accountInformation->getSupportUrl());
    $this->assertEquals(1, $accountInformation->getMaxAttempts());
    $this->assertEquals("PayXpert Payment Service", $accountInformation->getNotificationSenderName());
    $this->assertEquals("support@payxpert.com", $accountInformation->getNotificationSenderEmail());
    $this->assertEquals(true, $accountInformation->getNotificationOnSuccess());
    $this->assertEquals(false, $accountInformation->getNotificationOnFailure());
    $this->assertEquals(true, $accountInformation->getMerchantNotification());
    $this->assertEquals("support@payxpert.com", $accountInformation->getMerchantNotificationTo());
    $this->assertEquals("fr", $accountInformation->getMerchantNotificationLang());

    $paymentMethods = $accountInformation->getPaymentMethods();
    $this->assertNotNull($paymentMethods);
    $this->assertEquals(13, count($paymentMethods));

    $pmCreditcardFound = false;
    $pmDirectDebitFound = false;
    $pmBankTransferSofortFound = false;
    $pmBankTransferEPSFound = false;
    $pmBankTransferDragonPayFound = false;
    $pmBankTransferTrustlyFound = false;
    $pmBankTransferIDealFound = false;
    $pmBankTransferPoliFound = false;
    $pmBankTransferPrzelewy24Found = false;
    $pmBankTransferGiropayFound = false;
    $pmToditoFound = false;
    $pmWeChatFound = false;
    $pmLineFound = false;

    foreach ($paymentMethods as $paymentMethod) {
      switch ($paymentMethod->getPaymentMethod()) {
        case Connect2PayClient::PAYMENT_METHOD_CREDITCARD:
          $pmCreditcardFound = true;

          $this->assertNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("authorize", $paymentMethod->getDefaultOperation());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(2, count($currencies));
          $this->assertTrue(in_array("EUR", $currencies));
          $this->assertTrue(in_array("USD", $currencies));

          $options = $paymentMethod->getOptions();
          $this->assertNotNull($options);
          $this->assertEquals(1, count($options));
          $this->assertEquals("3dsMode", $options[0]->getName());
          $this->assertEquals("honor", $options[0]->getValue());

          break;
        case Connect2PayClient::PAYMENT_METHOD_DIRECTDEBIT:
          $pmDirectDebitFound = true;

          $this->assertNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("submission", $paymentMethod->getDefaultOperation());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(1, count($currencies));
          $this->assertTrue(in_array("EUR", $currencies));

          $this->assertNull($paymentMethod->getOptions());

          break;
        case Connect2PayClient::PAYMENT_METHOD_BANKTRANSFER:
          $this->assertNotNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
          $this->assertNull($paymentMethod->getOptions());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(1, count($currencies));

          switch ($paymentMethod->getPaymentNetwork()) {
            case Connect2PayClient::PAYMENT_NETWORK_SOFORT:
              $pmBankTransferSofortFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_EPS:
              $pmBankTransferEPSFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_DRAGONPAY:
              $pmBankTransferDragonPayFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_TRUSTLY:
              $pmBankTransferTrustlyFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_IDEAL:
              $pmBankTransferIDealFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_POLI:
              $pmBankTransferPoliFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_PRZELEWY24:
              $pmBankTransferPrzelewy24Found = true;

              $this->assertTrue(in_array("PLN", $currencies));

              break;
            case Connect2PayClient::PAYMENT_NETWORK_GIROPAY:
              $pmBankTransferGiropayFound = true;

              $this->assertTrue(in_array("EUR", $currencies));

              break;
            default:
              throw new \Exception("Unexpected payment network found: " . $paymentMethod->getPaymentNetwork());
          }

          break;
        case Connect2PayClient::PAYMENT_METHOD_TODITOCASH:
          $pmToditoFound = true;

          $this->assertNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(2, count($currencies));
          $this->assertTrue(in_array("EUR", $currencies));
          $this->assertTrue(in_array("MXN", $currencies));

          $this->assertNull($paymentMethod->getOptions());

          break;
        case Connect2PayClient::PAYMENT_METHOD_WECHAT:
          $pmWeChatFound = true;

          $this->assertNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(2, count($currencies));
          $this->assertTrue(in_array("EUR", $currencies));
          $this->assertTrue(in_array("USD", $currencies));
          $this->assertNull($paymentMethod->getOptions());

          break;
        case Connect2PayClient::PAYMENT_METHOD_LINE:
          $pmLineFound = true;

          $this->assertNull($paymentMethod->getPaymentNetwork());
          $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

          $currencies = $paymentMethod->getCurrencies();
          $this->assertNotNull($currencies);
          $this->assertEquals(1, count($currencies));
          $this->assertTrue(in_array("TWD", $currencies));
          $this->assertNull($paymentMethod->getOptions());

          break;
        default:
          throw new \Exception("Unexpected payment method found: " . $paymentMethod->getPaymentMethod());
      }
    }

    $this->assertTrue($pmCreditcardFound);
    $this->assertTrue($pmDirectDebitFound);
    $this->assertTrue($pmBankTransferSofortFound);
    $this->assertTrue($pmBankTransferEPSFound);
    $this->assertTrue($pmBankTransferDragonPayFound);
    $this->assertTrue($pmBankTransferTrustlyFound);
    $this->assertTrue($pmBankTransferIDealFound);
    $this->assertTrue($pmBankTransferPoliFound);
    $this->assertTrue($pmBankTransferPrzelewy24Found);
    $this->assertTrue($pmBankTransferGiropayFound);
    $this->assertTrue($pmToditoFound);
    $this->assertTrue($pmWeChatFound);
    $this->assertTrue($pmLineFound);
  }
}
