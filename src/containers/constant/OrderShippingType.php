<?php

namespace PayXpert\Connect2Pay\containers\constant;

class OrderShippingType
{
    const TO_NON_BILLING_ADDRESS = "03";
    const TO_VERIFIED_ADDRESS = "02";
    const TRAVEL_EVENT_TICKET = "06";
    const SHIP_TO_STORE = "04";
    const DIGITAL_GOODS = "05";
    const OTHER = "07";
    const TO_CARDHOLDER = "01";
}