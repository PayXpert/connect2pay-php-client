<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\containers;

class AliPayDirectProcessResponse extends Container
{
    private $apiVersion;
    private $code;
    private $message;
    private $exchangeRate;
    private $qrCode;
    private $qrCodeUrl;
    private $webSocketUrl;
    private $transactionID;
    private $transactionInfo;
    // SDK mode field
    private $rawRequest;

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    public function getQrCode()
    {
        return $this->qrCode;
    }

    public function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function getQrCodeUrl()
    {
        return $this->qrCodeUrl;
    }

    public function setQrCodeUrl($qrCodeUrl)
    {
        $this->qrCodeUrl = $qrCodeUrl;
        return $this;
    }

    public function getWebSocketUrl()
    {
        return $this->webSocketUrl;
    }

    public function setWebSocketUrl($webSocketUrl)
    {
        $this->webSocketUrl = $webSocketUrl;
        return $this;
    }

    public function getTransactionID()
    {
        return $this->transactionID;
    }

    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
        return $this;
    }

    public function getTransactionInfo()
    {
        return $this->transactionInfo;
    }

    public function setTransactionInfo($transactionInfo)
    {
        $this->transactionInfo = $transactionInfo;
        return $this;
    }

    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    public function setRawRequest($rawRequest)
    {
        $this->rawRequest = $rawRequest;
        return $this;
    }

    public static function getFromJson($dataJson)
    {
        $response = null;

        if ($dataJson != null && is_object($dataJson)) {
            // Root element, AccountInformation
            $response = new AliPayDirectProcessResponse();
            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\AliPayDirectProcessResponse');
            self::copyScalarProperties($reflector->getProperties(), $dataJson, $response);

            // Transaction information
            if (isset($dataJson->transactionInfo)) {
                $response->transactionInfo = containers\TransactionAttempt::getFromJson($dataJson->transactionInfo);
            }
        }

        return $response;
    }
}