<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\constant\PaymentNetwork;
use PayXpert\Connect2Pay\containers\response\AccountInformation;
use PayXpert\Connect2Pay\Connect2PayClient;

/**
 *
 * @covers AccountInformation
 */
final class AccountInformationTest extends CommonTest
{

    public function testGetFromJson()
    {
        $accountInformation = AccountInformation::getFromJson(Connect2PayAPIMock::getAccountInformationMock());

        $this->assertNotNull($accountInformation);
        $this->assertEquals("002.70", $accountInformation->getApiVersion());
        $this->assertEquals("Unit Test Website", $accountInformation->getName());
        $this->assertEquals(false, $accountInformation->getDisplayTerms());
        $this->assertEquals("", $accountInformation->getTermsUrl());
        $this->assertEquals("", $accountInformation->getSupportUrl());
        $this->assertEquals(1, $accountInformation->getMaxAttempts());
        $this->assertEquals("PayXpert Payment Service", $accountInformation->getNotificationSenderName());
        $this->assertEquals("support@payxpert.com", $accountInformation->getNotificationSenderEmail());
        $this->assertEquals(true, $accountInformation->getNotificationOnSuccess());
        $this->assertEquals(true, $accountInformation->getNotificationOnFailure());
        $this->assertEquals(true, $accountInformation->getMerchantNotification());
        $this->assertEquals("support@payxpert.com", $accountInformation->getMerchantNotificationTo());
        $this->assertEquals("fr", $accountInformation->getMerchantNotificationLang());

        $paymentMethods = $accountInformation->getPaymentMethods();
        $this->assertNotNull($paymentMethods);
        $this->assertEquals(16, count($paymentMethods));

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
        $pmUnionPayFound = false;
        $pmToditoFound = false;
        $pmWeChatFound = false;
        $pmAlipayFound = false;
        $pmLineFound = false;
        $pmBancontactFound = false;

        foreach ($paymentMethods as $paymentMethod) {
            switch ($paymentMethod->getPaymentMethod()) {
                case PaymentMethod::CREDIT_CARD:
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
                    $this->assertEquals(2, count($options));
                    $this->assertEquals("3dsMode", $options[0]->getName());
                    $this->assertEquals("honor", $options[0]->getValue());
                    $this->assertEquals("3dsVersion", $options[1]->getName());
                    $this->assertEquals("v2", $options[1]->getValue());

                    break;
                case PaymentMethod::DIRECT_DEBIT:
                    $pmDirectDebitFound = true;

                    $this->assertNull($paymentMethod->getPaymentNetwork());
                    $this->assertEquals("submission", $paymentMethod->getDefaultOperation());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));
                    $this->assertTrue(in_array("EUR", $currencies));

                    $this->assertNull($paymentMethod->getOptions());

                    break;
                case PaymentMethod::BANK_TRANSFER:
                    $this->assertNotNull($paymentMethod->getPaymentNetwork());
                    $this->assertNull($paymentMethod->getOptions());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));

                    switch ($paymentMethod->getPaymentNetwork()) {
                        case PaymentNetwork::SOFORT:
                            $pmBankTransferSofortFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::EPS:
                            $pmBankTransferEPSFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::DRAGONPAY:
                            $pmBankTransferDragonPayFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::TRUSTLY:
                            $pmBankTransferTrustlyFound = true;

                            $this->assertEquals("submission", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::IDEAL:
                            $pmBankTransferIDealFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::POLI:
                            $pmBankTransferPoliFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        case PaymentNetwork::PRZELEWY24:
                            $pmBankTransferPrzelewy24Found = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("PLN", $currencies));

                            break;
                        case PaymentNetwork::GIROPAY:
                            $pmBankTransferGiropayFound = true;

                            $this->assertEquals("sale", $paymentMethod->getDefaultOperation());
                            $this->assertTrue(in_array("EUR", $currencies));

                            break;
                        default:
                            throw new \Exception("Unexpected payment network found: " . $paymentMethod->getPaymentNetwork());
                    }

                    break;
                case PaymentMethod::UNIONPAY:
                    $pmUnionPayFound = true;

                    $this->assertNull($paymentMethod->getPaymentNetwork());
                    $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));
                    $this->assertTrue(in_array("EUR", $currencies));

                    $this->assertNull($paymentMethod->getOptions());

                    break;
                case PaymentMethod::TODITO_CASH:
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
                case PaymentMethod::WECHAT:
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
                case PaymentMethod::ALIPAY:
                    $pmAlipayFound = true;

                    $this->assertNull($paymentMethod->getPaymentNetwork());
                    $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));
                    $this->assertTrue(in_array("EUR", $currencies));
                    $this->assertNull($paymentMethod->getOptions());

                    break;
                case PaymentMethod::LINE:
                    $pmLineFound = true;

                    $this->assertNull($paymentMethod->getPaymentNetwork());
                    $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));
                    $this->assertTrue(in_array("TWD", $currencies));
                    $this->assertNull($paymentMethod->getOptions());

                    break;
                case PaymentMethod::BANCONTACT:
                    $pmBancontactFound = true;

                    $this->assertNull($paymentMethod->getPaymentNetwork());
                    $this->assertEquals("sale", $paymentMethod->getDefaultOperation());

                    $currencies = $paymentMethod->getCurrencies();
                    $this->assertNotNull($currencies);
                    $this->assertEquals(1, count($currencies));
                    $this->assertTrue(in_array("EUR", $currencies));
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
        $this->assertTrue($pmAlipayFound);
        $this->assertTrue($pmLineFound);
        $this->assertTrue($pmBancontactFound);
    }
}
