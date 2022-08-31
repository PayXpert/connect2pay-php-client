<?php

namespace PayXpert\Connect2Pay\containers\response;

class CreditCardPaymentMeanInfo
{
    /**
     * The truncated card number used for this transaction
     *
     * @var string
     */
    private $cardNumber;

    /**
     * The card expiration year
     *
     * @var string
     */
    private $cardExpireYear;

    /**
     * The card expire month
     *
     * @var string
     */
    private $cardExpireMonth;

    /**
     * The token associated with the card (if enabled for the account)
     *
     * @var string
     */
    private $cardToken;

    /**
     * The name of the holder of the card
     *
     * @var string
     */
    private $cardHolderName;

    /**
     * Brand of the card (Visa, Mcrd...)
     *
     * @var string
     */
    private $cardBrand;

    /**
     * Level of the card.
     * Special permission needed
     *
     * @var string
     */
    private $cardLevel;

    /**
     * Sub type of the card.
     * Special permission needed.
     *
     * @var string
     */
    private $cardSubType;

    /**
     * ISO2 country code of the issuer of the card.
     * Special permission needed.
     *
     * @var string
     */
    private $iinCountry;

    /**
     * Card Issuer Bank Name.
     * Special permission needed.
     *
     * @var string
     */
    private $iinBankName;

    /**
     * The liability shift for 3D Secure.
     * Can be true or false
     *
     * @var boolean
     */
    private $is3DSecure;

    /**
     * Credit Card Descriptor for this transaction
     *
     * @var String
     */
    private $statementDescriptor;

    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }

    public function getCardExpireYear()
    {
        return $this->cardExpireYear;
    }

    public function setCardExpireYear($cardExpireYear)
    {
        $this->cardExpireYear = $cardExpireYear;
        return $this;
    }

    public function getCardExpireMonth()
    {
        return $this->cardExpireMonth;
    }

    public function setCardExpireMonth($cardExpireMonth)
    {
        $this->cardExpireMonth = $cardExpireMonth;
        return $this;
    }

    public function getCardToken()
    {
        return $this->cardToken;
    }

    public function setCardToken($cardToken)
    {
        $this->cardToken = $cardToken;
        return $this;
    }

    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    public function setCardHolderName($cardHolderName)
    {
        $this->cardHolderName = $cardHolderName;
        return $this;
    }

    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    public function setCardBrand($cardBrand)
    {
        $this->cardBrand = $cardBrand;
        return $this;
    }

    public function getCardLevel()
    {
        return $this->cardLevel;
    }

    public function setCardLevel($cardLevel)
    {
        $this->cardLevel = $cardLevel;
        return $this;
    }

    public function getCardSubType()
    {
        return $this->cardSubType;
    }

    public function setCardSubType($cardSubType)
    {
        $this->cardSubType = $cardSubType;
        return $this;
    }

    public function getIinCountry()
    {
        return $this->iinCountry;
    }

    public function setIinCountry($iinCountry)
    {
        $this->iinCountry = $iinCountry;
        return $this;
    }

    public function getIinBankName()
    {
        return $this->iinBankName;
    }

    public function setIinBankName($iinBankName)
    {
        $this->iinBankName = $iinBankName;
        return $this;
    }

    public function getIs3DSecure()
    {
        return $this->is3DSecure;
    }

    public function setIs3DSecure($is3DSecure)
    {
        $this->is3DSecure = $is3DSecure;
        return $this;
    }

    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
        return $this;
    }
}