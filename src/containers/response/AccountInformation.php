<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\response\PaymentMethodInformation;
use PayXpert\Connect2Pay\containers\response\PaymentMethodOption;

/**
 * Contains the information returned by the account information API call
 */
class AccountInformation extends Container
{
    /**
     * API version of the response
     *
     * @var string
     */
    private $apiVersion;

    /**
     * The displayed name of the account
     *
     * @var string
     */
    private $name;

    /**
     * Indicates if Terms and conditions must be acknowledged on the payment page
     * by the shopper
     *
     * @var boolean
     */
    private $displayTerms;

    /**
     * Terms and conditions URL
     *
     * @var string
     */
    private $termsUrl;

    /**
     * URL of customer support
     *
     * @var string
     */
    private $supportUrl;

    /**
     * The number of attempts allowed to process the payment in case of failure
     *
     * @var integer
     */
    private $maxAttempts;

    /**
     * Name displayed in customers notification emails
     *
     * @var string
     */
    private $notificationSenderName;

    /**
     * Email displayed in customers notification emails
     *
     * @var string
     */
    private $notificationSenderEmail;

    /**
     * Indicates if a notification is sent to the shopper in case of payment
     * success
     *
     * @var boolean
     */
    private $notificationOnSuccess;

    /**
     * Indicates if a notification is sent to the shopper in case of payment
     * failure
     *
     * @var boolean
     */
    private $notificationOnFailure;

    /**
     * Indicates if a notification is sent to the merchant after a payment
     *
     * @var boolean
     */
    private $merchantNotification;

    /**
     * Email used to send merchant notification
     *
     * @var string
     */
    private $merchantNotificationTo;

    /**
     * The language used in merchant email notification (ISO-639 two letters code)
     *
     * @var string
     */
    private $merchantNotificationLang;

    /**
     * A list of payment methods available for the account
     *
     * @var PaymentMethodInformation[]
     */
    private $paymentMethods;

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getDisplayTerms()
    {
        return $this->displayTerms;
    }

    public function setDisplayTerms($displayTerms)
    {
        $this->displayTerms = $displayTerms;
        return $this;
    }

    public function getTermsUrl()
    {
        return $this->termsUrl;
    }

    public function setTermsUrl($termsUrl)
    {
        $this->termsUrl = $termsUrl;
        return $this;
    }

    public function getSupportUrl()
    {
        return $this->supportUrl;
    }

    public function setSupportUrl($supportUrl)
    {
        $this->supportUrl = $supportUrl;
        return $this;
    }

    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    public function getNotificationSenderName()
    {
        return $this->notificationSenderName;
    }

    public function setNotificationSenderName($notificationSenderName)
    {
        $this->notificationSenderName = $notificationSenderName;
        return $this;
    }

    public function getNotificationSenderEmail()
    {
        return $this->notificationSenderEmail;
    }

    public function setNotificationSenderEmail($notificationSenderEmail)
    {
        $this->notificationSenderEmail = $notificationSenderEmail;
        return $this;
    }

    public function getNotificationOnSuccess()
    {
        return $this->notificationOnSuccess;
    }

    public function setNotificationOnSuccess($notificationOnSuccess)
    {
        $this->notificationOnSuccess = $notificationOnSuccess;
        return $this;
    }

    public function getNotificationOnFailure()
    {
        return $this->notificationOnFailure;
    }

    public function setNotificationOnFailure($notificationOnFailure)
    {
        $this->notificationOnFailure = $notificationOnFailure;
        return $this;
    }

    public function getMerchantNotification()
    {
        return $this->merchantNotification;
    }

    public function setMerchantNotification($merchantNotification)
    {
        $this->merchantNotification = $merchantNotification;
        return $this;
    }

    public function getMerchantNotificationTo()
    {
        return $this->merchantNotificationTo;
    }

    public function setMerchantNotificationTo($merchantNotificationTo)
    {
        $this->merchantNotificationTo = $merchantNotificationTo;
        return $this;
    }

    public function getMerchantNotificationLang()
    {
        return $this->merchantNotificationLang;
    }

    public function setMerchantNotificationLang($merchantNotificationLang)
    {
        $this->merchantNotificationLang = $merchantNotificationLang;
        return $this;
    }

    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods($paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
        return $this;
    }

    public static function getFromJson($infoJson)
    {
        $accountInfo = null;

        if ($infoJson != null && is_object($infoJson)) {
            // Root element, AccountInformation
            $accountInfo = new AccountInformation();
            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\AccountInformation');
            self::copyScalarProperties($reflector->getProperties(), $infoJson, $accountInfo);

            // Payment Method Information
            if (isset($infoJson->paymentMethods) && is_array($infoJson->paymentMethods)) {
                $paymentMethods = array();

                foreach ($infoJson->paymentMethods as $paymentMethod) {
                    $pmi = new PaymentMethodInformation();
                    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\PaymentMethodInformation');
                    self::copyScalarProperties($reflector->getProperties(), $paymentMethod, $pmi);

                    // Currencies array
                    if (isset($paymentMethod->currencies) && is_array($paymentMethod->currencies)) {
                        $pmi->setCurrencies($paymentMethod->currencies);
                    }

                    // Payment Method Options
                    if (isset($paymentMethod->options) && is_array($paymentMethod->options)) {
                        $paymentMethodOptions = array();

                        foreach ($paymentMethod->options as $option) {
                            $pmo = new PaymentMethodOption();
                            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\PaymentMethodOption');
                            self::copyScalarProperties($reflector->getProperties(), $option, $pmo);

                            $paymentMethodOptions[] = $pmo;
                        }

                        $pmi->setOptions($paymentMethodOptions);
                    }

                    $paymentMethods[] = $pmi;
                }

                $accountInfo->setPaymentMethods($paymentMethods);
            }
        }

        return $accountInfo;
    }
}