<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\response\SepaMandate;

class BankAccount
{
    /**
     * The account holder name
     *
     * @var string
     */
    private $holderName;

    /**
     * Name of the bank of the account
     *
     * @var string
     */
    private $bankName;

    /**
     * IBAN number of the account (truncated)
     *
     * @var string
     */
    private $iban;

    /**
     * BIC number of the account
     *
     * @var string
     */
    private $bic;

    /**
     * ISO2 country code of the account
     *
     * @var string
     */
    private $countryCode;

    /**
     * The optional SEPA mandate associated with the account
     *
     * @var SepaMandate
     */
    private $sepaMandate;

    public function getHolderName()
    {
        return $this->holderName;
    }

    public function setHolderName($holderName)
    {
        $this->holderName = $holderName;
        return $this;
    }

    public function getBankName()
    {
        return $this->bankName;
    }

    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
        return $this;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function setIban($iban)
    {
        $this->iban = $iban;
        return $this;
    }

    public function getBic()
    {
        return $this->bic;
    }

    public function setBic($bic)
    {
        $this->bic = $bic;
        return $this;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getSepaMandate()
    {
        return $this->sepaMandate;
    }

    public function setSepaMandate($sepaMandate)
    {
        $this->sepaMandate = $sepaMandate;
        return $this;
    }
}