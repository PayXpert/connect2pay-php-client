<?php

namespace PayXpert\Connect2Pay\containers\constant;

class OrderType
{
    const GOODS_SERVICE = "01";
    const CHECK_ACCEPTANCE = "03";
    const ACCOUNT_FUNDING = "10";
    const QUASI_CASH = "11";
    const PREPAID_LOAN = "28";
}