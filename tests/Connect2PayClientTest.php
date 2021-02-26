<?php

namespace PayXpert\Connect2Pay\Tests;

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\AccountAge;
use PayXpert\Connect2Pay\containers\response\PaymentStatus;

/**
 * @covers Connect2PayClient
 */
final class Connect2PayClientTest extends CommonTest
{

    public function testConfiguration()
    {
        $this->assertEquals($this->c2pClient->getMerchant(), $this->originator);
        $this->assertEquals($this->c2pClient->getPassword(), $this->password);
        $this->assertEquals($this->c2pClient->getUrl(), $this->connect2pay);
    }

    public function testHandleRedirectStatus()
    {
        $merchantToken = "sxbJyd57aaawXB7s6sISew";
        $data = "Nmw3RZdsfQerTotBGaoiHRHt6ZxfJXeJfskO6nEfZnlYFVtYVHUJAx6OK_ToSGH10L-5-XdqtnzvwS5-bih0yPm0WvBAcmyogMbEMgx2MzhdKW5AWfnOAcSlaNsueT85GyzgCOi1DwIHo0XNjQIYM2p3ajCtoqnWO2OrM4t2bIs8akyQay4m9F6rkOj_lH6Wlo8N-8Qv8mJF29o677mr83HrDzwz9-k3nvvLDY0m9c440AR2JhIxZGnQ6DKi7BTSPubpjUBFrFXUXREcmnergW4urjKcjBKje2UR9qW0olvVEH_TzUJ76CgQLoDXSX1grrvMEFAaGlKpP1WDI5wtkC1s58B4LMNdz6O1-gZdjRFf6F6SIeuidoGfPQ5b7op_q3ZvKERfYlNv6eOVgvm7eV9qcT35cSkmoez1v7WO00yNGDZTwiTu_WxcQ2misv46oGLYMd914IrGxcrHAwfKdpCg54wGOoZGiHiLelB1okUTrsbtYXXnhV_3jaUakafPKCnLkbjwdihrs5WSRVAopt69z91_DlNJXYIelTpGIzANzQEqx-C6trkEoyjqreoUQUWRwjge9OzONaoSYbjiMtbwpbtidpTbZK6IUZCUawmnDE_WVXZtH0BX4uuGA5Azp5W3riOFvclh2ijPhHOuxiXi6Y2LWgLqyFkhVI9GA8lZxSH619KPht3dGJTpldAjuxkSyHn0GQA1AzkZ1VFw4lEJ6-zl0GFIJEG8aknwSGAZ6L64G02-C5D7T0D-OVj-LhnDZY7IQavcRUOc3PiScrCmq5KPpbSTErjbYgCo8ffW-GacMcMq_vnEklUz-A8td-tgLWPhADn0bcpfimpyGRVZUTCiy2KXO2D6RxtuMJxacEqNcUTKE48Wb3wNmSAGgIW8PMOmQbF4kmi0z9DVKFkEJK1CMKpliMn17sfxz-bBmvS0dcvf3PR0al5X6JG3ipmpjYtKNDLqZaaQl1DVmYJyxoe7g39dn7rDx6b4R8rv-cCorN6e6YdcN-7j74IcPL2FBryki3MZb7AOaua-XbzjP2PYq6YiqOsQu78YaKqPiREbel75hTgM6Lx24jZDvU_7P_BRty6lrqCW6sIcRvcCwsnl2wx2OT3q_jr6lRGl2pL8L8c3AS92ntNPcGxl3MHugqv-FXBNkG6onTyFkplAnRMhLvTeCmZHaCaWodoYfheuG6c0RbmIw6SVhZcJlr-KwyI270BWKaGhWhN26Xz4SMn7XwZziUfxQeVkYDnHdOvc7ujQLZdhrqIoOA88JCWekGFsKAIOc-qbJ5vGQr2X5D0cf7rzEVyNf1XhMwCYpiNHd1qApcGD3UlqBCtAOArKr_cyz0nTHDmVW9lfLfj2ulS_UiheXQEIS_W2Ej0QpDgRTORFyd-jOMoL8Yhr35pFMj-rwNpzb44Ixmz8tRv0JQr1mcKwgd-NmwWbRRSxqyq3iySnWuIQ00q8QruyRPHbLeqQU9aVFCHkN3t-_C_KLGpw_3EXI2PliXn4_0Rl3iOAdy6DV8ry1Vw7XNX1LUC0t64fuX6YX6AQfMRFeiYNuyMZwHRu_lj8Z65H9aumHCagPubUM6Eeoljuef3C2fB7LNOuL0RwEJEFuFdI7YUKgxsJFWuo9dhlx1HMqNO7oouiVNeXIDtrU__nRw36oarJY2pQk-bVt5f1uS-oZRTTaKXEYpYGOMQhQwQiKYQ-YDYjxc_kgOrBQHHnehEW-N8cTcOqiaOf964bGs_blPD0axIzALgIMdpoYbWbu-nUxt2qyKO9nyHMS2uiKupFHi04a_wdKQCxyFTzPvL8meZLHImCd99dHShqQuCWREWB14oqelIg89xclN_jZaF5kduwefphicDYBofaFF8Htyih2WtLwKLbSkDJqYtR66Fjx0PyYPDv_lppp4LRr3r4M0jq-Gp1q299V8RSfKai0SXSBHvvx-TdgVZu3uy7PfPO5UNrbLzoaldX5QZnx4zlehwOAfSYj92GatlKfXpTgPjvLUgXbTUOQbA2Y1caQdiU_cZsGgcmFIhQlsMYmwnKYsKaq4V1RXwPrfgTlITprAeHBzWiwK7zF4ae-lFWow7r3PqY-IJXFdDuH-7qmffY8K39DGmYbqDuP81HijHxVdazvlu0FDt5-k_w9rlSzVf3jmWFQ4lD7WjGLtliHotDTVMtqoq0yushrktKM7GSRwf4zOfkN0NrvfISkW6xBCukAtngsNg3W1QVgDJoVQEK";

        $this->assertTrue($this->c2pClient->handleRedirectStatus($data, $merchantToken));
        $status = $this->c2pClient->getStatus();

        $this->assertNotNull($status);
        $this->assertEquals("authorize", $status->getOperation());
        $this->assertEquals(100, $status->getAmount());
        $this->assertEquals("000", $status->getErrorCode());
        $this->assertEquals("Transaction successfully completed", $status->getErrorMessage());

        $order = $status->getOrder();
        $this->assertNotNull($order);
        $this->assertEquals("2020-12-31-11.55.07", $order->getId());

        $transaction = $status->getLastInitialTransactionAttempt();
        $this->assertNotNull($transaction);
        $this->assertEquals("5643604", $transaction->getTransactionID());

        $shopper = $transaction->getShopper();
        $this->assertNotNull($shopper);
        $this->assertEquals("John", $shopper->getFirstName());
        $this->assertEquals("Doe", $shopper->getLastName());
        $this->assertEquals("US", $shopper->getCountryCode());

        $account = $shopper->getAccount();
        $this->assertNotNull($account);
        $this->assertEquals(AccountAge::DURING_TRANSACTION, $account->getAge());
    }
}
