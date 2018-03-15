<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\Connect2PayClient;

/**
 * @covers Connect2PayClient
 */
final class Connect2PayClientTest extends CommonTest {

  public function testConfiguration() {
    $this->assertEquals($this->c2pClient->getMerchant(), $this->originator);
    $this->assertEquals($this->c2pClient->getPassword(), $this->password);
    $this->assertEquals($this->c2pClient->getUrl(), $this->connect2pay);
  }

  public function testValidateNoRequiredParameter() {
    $this->assertFalse($this->c2pClient->validate());
  }

  public function testValidateParameterRequired() {
    $this->c2pClient->setOrderID(date("Y-m-d-H.i.s"));
    $this->c2pClient->setCurrency("EUR");
    $this->c2pClient->setAmount(1216);
    $this->c2pClient->setShippingType(Connect2PayClient::_SHIPPING_TYPE_VIRTUAL);
    $this->c2pClient->setPaymentMode(Connect2PayClient::_PAYMENT_MODE_SINGLE);

    $this->assertTrue($this->c2pClient->validate());
  }

  public function testValidateParameterSize() {
    $this->orderDetails();
    $this->c2pClient->setCurrency("EURO");

    $this->assertFalse($this->c2pClient->validate());
    $this->assertEquals("currency Length 3 * ", $this->c2pClient->getClientErrorMessage());
  }

  public function testHandleRedirectStatus() {
    $merchantToken = "F0b53GMmkiBZ7zy6eNFRuw";
    $data = "NeVoQsYVWhVOF1T3oOboM1H9halv8l93cMZCIdhuaIL02ERtXopxLtmz-i-kt1zo9MpSkg_W5F1xy84TEPusJ0Rdaxxe8IvkUfDCptI0SDqCWw9QN_TzNPlhkPINxTMAhyQK45ySfRmhc-rr09CqjL9oInU6_TxfBIZzxStPsq_mA0Dh7JHxOhO4oeXPMqwrvstVk93J0X_J-cQOVwb5584x9w8udDUWCPLYfToc1D_RU-oQ3Z4PYNmKCzJGazDFhno9UpkPN7f7ikbHSMGcZNarpq7ip6c6U-vtnGbFD-FSpb5Z10qXVNLIb0vapHT5Js_QCqusSXqbbWhnDk2PjPZJMOlIIcs459mMjcgyyBsC8LrNqoCTB7uVP2RUP3osBSBWUGZdY8JM8kmRcRGXJgkyOXseM-sd0R89zhc_koZvMoZtoJfAkz1_LqQIxa0vBWKkJeDxnrLxcqgV4VA8ryHTxCi8phY7i_MzrAixkhv1rT5kd6hCQa8sJUX8dwiYl6fDoGUn1jPaUJRPQ4wqEK9Xumy-ykzbcUYCumLH5ynQ6wIG8Uqfjeu69xnB_T2rB4uqt404IOKJ4JiH3Ldke6TbMQ3I4BCGJekXZaCLKyU7MomiptMIpBAP9VsS9gTTHt85Vo57ot6K5jN4J8kK_TnmefIzNy7RUQriyHuYayaHlbWLtktWrYBcfNBPjTk6DJdMOeOelZUBgE0G2re20TwdtulmN7Z5DmW4ZrEfDlz4LY2k3C7wrEX7wIoxzUlD-c_11OxL7Y65dAUlXBbVbgQBQolLtZdWXwAoreot3ZuiAW_43kqtTTlJwi_hErHZjDvW3Mo2s8B6jBupw6_R5fv-mxJehviwrwgp66E6ghf38Fa-M81rOoOelYlme0iFFifXGU2WounKgleQKBEHiI02VJ4xwglmp4aJXXo9oCKki61F4bbq8wI99IfkcPqeMeKRjGWzCBflMlQQtJjkbLqbPw_xMbZP6psgerEl8H090Uc8Vj4rRl6LAcrojmIKQKX5oPxeBC13ySonWVAwglZZ3ZcrLy9gNtQ-zO5xg_QEAUKJS7WXVl8AKK3qLd2bogFv-N5KrU05ScIv4RKx2VPwMfKxvX0nHMg8JP3smNadXRXZMcGcIo2vLwSArIlh7dn5bJxjoEfIRQs7WgJJuhwjfOvJEU95f7jmZn0ge7pBxkrdh0j4SWZSBycKveX6N_vevE1GpvaF37bawzTVpbG0T1az2k-bPEpYbuC4YY1gSDzubstpeIfy6U6w3Q_L";

    $this->assertTrue($this->c2pClient->handleRedirectStatus($data, $merchantToken));

    $status = $this->c2pClient->getStatus();

    $this->assertNotNull($status);
    $this->assertEquals("2018-03-13-18.10.43", $status->getOrderID());
    $this->assertEquals("authorize", $status->getOperation());
    $this->assertEquals(100, $status->getAmount());
    $this->assertEquals("000", $status->getErrorCode());
    $this->assertEquals("Transaction successfully completed", $status->getErrorMessage());

    $transaction = $status->getLastInitialTransactionAttempt();
    $this->assertNotNull($transaction);
    $this->assertEquals("10003184", $transaction->getTransactionID());

    $shopper = $transaction->getShopper();
    $this->assertNotNull($shopper);
    $this->assertEquals("John Doe", $shopper->getName());
    $this->assertEquals("US", $shopper->getCountryCode());
  }

  /*
   * TODO
   *
   */

  // public function testPreparePayment()
  // {
  // $this->orderDetails();
  // }
  private function orderDetails() {
    $this->c2pClient->setOrderID(date("Y-m-d-H.i.s"));
    $this->c2pClient->setCurrency("EUR");
    $this->c2pClient->setAmount(1216);
    $this->c2pClient->setShippingType(Connect2PayClient::_SHIPPING_TYPE_VIRTUAL);
    $this->c2pClient->setPaymentMode(Connect2PayClient::_PAYMENT_MODE_SINGLE);
  }
}
