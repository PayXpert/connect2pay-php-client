<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;
use ReflectionClass;

class UpiDirectProcessResponse extends Container
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

    public function setApiVersion($apiVersion): UpiDirectProcessResponse
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code): UpiDirectProcessResponse
    {
        $this->code = $code;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message): UpiDirectProcessResponse
    {
        $this->message = $message;
        return $this;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate($exchangeRate): UpiDirectProcessResponse
    {
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    public function getQrCode()
    {
        return $this->qrCode;
    }

    public function setQrCode($qrCode): UpiDirectProcessResponse
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function getQrCodeUrl()
    {
        return $this->qrCodeUrl;
    }

    public function setQrCodeUrl($qrCodeUrl): UpiDirectProcessResponse
    {
        $this->qrCodeUrl = $qrCodeUrl;
        return $this;
    }

    public function getWebSocketUrl()
    {
        return $this->webSocketUrl;
    }

    public function setWebSocketUrl($webSocketUrl): UpiDirectProcessResponse
    {
        $this->webSocketUrl = $webSocketUrl;
        return $this;
    }

    public function getTransactionID()
    {
        return $this->transactionID;
    }

    public function setTransactionID($transactionID): UpiDirectProcessResponse
    {
        $this->transactionID = $transactionID;
        return $this;
    }

    public function getTransactionInfo()
    {
        return $this->transactionInfo;
    }

    public function setTransactionInfo($transactionInfo): UpiDirectProcessResponse
    {
        $this->transactionInfo = $transactionInfo;
        return $this;
    }

    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    public function setRawRequest($rawRequest): UpiDirectProcessResponse
    {
        $this->rawRequest = $rawRequest;
        return $this;
    }

    public static function getFromJson($dataJson): ?UpiDirectProcessResponse
    {
        $response = null;

        if ($dataJson != null && is_object($dataJson)) {
            // Root element, AccountInformation
            $response = new UpiDirectProcessResponse();
            $reflector = new ReflectionClass('PayXpert\Connect2Pay\containers\response\UpiDirectProcessResponse');
            self::copyScalarProperties($reflector->getProperties(), $dataJson, $response);

            // Transaction information
            if (isset($dataJson->transactionInfo)) {
                $response->transactionInfo = TransactionAttempt::getFromJson($dataJson->transactionInfo);
            }
        }

        return $response;
    }
}