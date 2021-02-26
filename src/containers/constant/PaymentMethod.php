<?php

namespace PayXpert\Connect2Pay\containers\constant;

class PaymentMethod
{
    const CREDIT_CARD = 'CreditCard';
    /**
     * @deprecated Unsupported payment method
     */
    const TODITO_CASH = 'ToditoCash';
    const BANK_TRANSFER = 'BankTransfer';
    const DIRECT_DEBIT = 'DirectDebit';
    const WECHAT = 'WeChat';
    const LINE = 'Line';
    const ALIPAY = 'Alipay';
    const UNIONPAY = 'UnionPay';
    const BANCONTACT = 'Bancontact';
}