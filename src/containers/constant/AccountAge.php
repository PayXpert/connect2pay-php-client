<?php

namespace PayXpert\Connect2Pay\containers\constant;

class AccountAge
{
    const NO_ACCOUNT = "01";
    const DURING_TRANSACTION = "02";
    const LESS_30_DAYS = "03";
    const BETWEEN_30_60_DAYS = "04";
    const MORE_60_DAYS = "05";
}