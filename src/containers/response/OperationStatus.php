<?php

namespace PayXpert\Connect2Pay\containers\response;

class OperationStatus
{
    /**
     * @var Int
     */
    private $code;

    /**
     * @var String
     */
    private $message;

    /**
     * @var String
     */
    private $transactionID;

    /**
     * @var String
     */
    private $operation;

    /**
     * Result code of the operation call
     *
     * @return Int
     */
    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Error message of the operation call
     *
     * @return String
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Transaction identifier of operation transaction.
     *
     * @return String
     */
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
        return $this;
    }

    /**
     * The effective operation done by the call
     *
     * @return String
     */
    public function getOperation()
    {
        return $this->operation;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }
}