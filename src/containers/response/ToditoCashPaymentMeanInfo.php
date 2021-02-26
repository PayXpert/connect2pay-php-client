<?php

namespace PayXpert\Connect2Pay\containers\response;

class ToditoCashPaymentMeanInfo
{
    /**
     * The truncated Todito card number used for this transaction
     *
     * @var string
     */
    private $cardNumber;

    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }
}