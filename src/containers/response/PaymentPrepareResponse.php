<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;

class PaymentPrepareResponse extends Container
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $customerToken;
    /**
     * @var string
     */
    private $merchantToken;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return PaymentPrepareResponse
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return PaymentPrepareResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerToken()
    {
        return $this->customerToken;
    }

    /**
     * @param string $customerToken
     * @return PaymentPrepareResponse
     */
    public function setCustomerToken($customerToken)
    {
        $this->customerToken = $customerToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantToken()
    {
        return $this->merchantToken;
    }

    /**
     * @param string $merchantToken
     * @return PaymentPrepareResponse
     */
    public function setMerchantToken($merchantToken)
    {
        $this->merchantToken = $merchantToken;
        return $this;
    }
}