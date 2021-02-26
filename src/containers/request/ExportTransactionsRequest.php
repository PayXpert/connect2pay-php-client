<?php

namespace PayXpert\Connect2Pay\containers\request;

class ExportTransactionsRequest
{
    private $apiVersion;
    private $startTime;
    private $endTime;
    private $operation;
    private $paymentMethod;
    private $paymentNetwork;
    private $transactionResultCode;

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function setPaymentNetwork($paymentNetwork)
    {
        $this->paymentNetwork = $paymentNetwork;
        return $this;
    }

    public function setTransactionResultCode($transactionResultCode)
    {
        $this->transactionResultCode = $transactionResultCode;
        return $this;
    }

    public function toParamsArray()
    {
        $array = array();

        foreach ($this as $key => $value) {
            if ($value !== null) {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}