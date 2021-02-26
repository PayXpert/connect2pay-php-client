<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\response\PaymentMethodOption;

class PaymentMethodInformation
{
    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentNetwork;

    /**
     * @var string[]
     */
    private $currencies;

    /**
     * @var string
     */
    private $defaultOperation;

    /**
     * @var PaymentMethodOption[]
     */
    private $options;

    /**
     * The payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * The payment network
     *
     * @return string
     */
    public function getPaymentNetwork()
    {
        return $this->paymentNetwork;
    }

    public function setPaymentNetwork($paymentNetwork)
    {
        $this->paymentNetwork = $paymentNetwork;
        return $this;
    }

    /**
     * A list of ISO 4217 currency code for which this payment method is enabled
     *
     * @return string[]
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    public function setCurrencies($currencies)
    {
        $this->currencies = $currencies;
        return $this;
    }

    /**
     * The operation that will be executed by default when processing this type of
     * method.
     * Can be sale, authorize or collection
     *
     * @return string
     */
    public function getDefaultOperation()
    {
        return $this->defaultOperation;
    }

    public function setDefaultOperation($defaultOperation)
    {
        $this->defaultOperation = $defaultOperation;
        return $this;
    }

    /**
     * A list of payment method specific option
     *
     * @return PaymentMethodOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}