<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\response\BankAccount;

class DirectDebitPaymentMeanInfo
{
    /**
     * Bank account
     *
     * @var BankAccount
     */
    private $bankAccount;

    /**
     * Statement Descriptor
     *
     * @var String
     */
    private $statementDescriptor;

    /**
     * Date of collection of the transaction
     *
     * @var integer
     */
    private $collectedAt;

    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
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

    public function getCollectedAt()
    {
        return $this->collectedAt;
    }

    public function getCollectedAtAsDateTime()
    {
        if ($this->collectedAt != null) {
            // API returns date as timestamp in milliseconds
            $timestamp = intval($this->collectedAt / 1000);
            return new \DateTime("@" . $timestamp);
        }

        return null;
    }

    public function setCollectedAt($collectedAt)
    {
        $this->collectedAt = $collectedAt;
        return $this;
    }
}