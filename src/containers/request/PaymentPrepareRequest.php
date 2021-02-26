<?php

namespace PayXpert\Connect2Pay\containers\request;

use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\constant\OperationType;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\constant\PaymentMode;
use PayXpert\Connect2Pay\containers\constant\PaymentNetwork;
use PayXpert\Connect2Pay\containers\constant\SubscriptionType;
use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\Order;
use PayXpert\Connect2Pay\containers\Shipping;
use PayXpert\Connect2Pay\containers\Shopper;
use PayXpert\Connect2Pay\helpers\C2PValidate;
use PayXpert\Connect2Pay\helpers\Utils;

class PaymentPrepareRequest extends Container
{
    private $apiVersion = Connect2PayClient::API_VERSION;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $paymentMode;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentNetwork;

    /**
     * @var string
     */
    private $operation;

    /**
     * @var bool
     */
    private $secure3d;

    /**
     * @var int
     */
    private $offerID;

    /**
     * @var string
     */
    private $subscriptionType;

    /**
     * @var string
     */
    private $trialPeriod;

    /**
     * @var int
     */
    private $rebillAmount;

    /**
     * @var string
     */
    private $rebillPeriod;

    /**
     * @var int
     */
    private $rebillMaxIteration;

    /**
     * @var string
     */
    private $ctrlRedirectURL;

    /**
     * @var string
     */
    private $ctrlCallbackURL;

    /**
     * @var string
     */
    private $ctrlCustomData;

    /**
     * @var int
     */
    private $themeID;

    /**
     * @var string
     */
    private $timeOut;

    /**
     * @var bool
     */
    private $merchantNotification;

    /**
     * @var string
     */
    private $merchantNotificationTo;

    /**
     * @var string
     */
    private $merchantNotificationLang;

    /**
     * @var Shopper
     */
    private $shopper;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * @var Order
     */
    private $order;

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Currency for the current order
     *
     * @param string $currency
     * @return PaymentPrepareRequest
     */
    public function setCurrency($currency)
    {
        $this->currency = $this->limitLength($currency, 3);
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * The transaction amount in cents (for 1€ => 100)
     *
     * @param int $amount
     * @return PaymentPrepareRequest
     */
    public function setAmount($amount)
    {
        if (C2PValidate::isInt($amount)) {
            $this->amount = (int) $amount;
        } else {
            Utils::error("Bad value for amount: " . $amount);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * Can be either : Single, OnShipping, Recurrent, InstalmentsPayments
     *
     * @param string $paymentMode
     * @return PaymentPrepareRequest
     */
    public function setPaymentMode($paymentMode)
    {
        $validValues = [
            PaymentMode::SINGLE,
            PaymentMode::ONSHIPPING,
            PaymentMode::RECURRENT,
            PaymentMode::INSTALMENTS
        ];

        if (in_array($paymentMode, $validValues)) {
            $this->paymentMode = $this->limitLength($paymentMode, 30);
        } else {
            Utils::error("Invalid paymentMode provided: " . $paymentMode);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Can be CreditCard, BankTransfer or empty.
     * This will change the type of the payment page displayed.
     * If empty, a selection page will be displayed to the customer with payment
     * types available for the account.
     *
     * @param string $paymentMethod
     * @return PaymentPrepareRequest
     */
    public function setPaymentMethod($paymentMethod)
    {
        $validValues = [
            PaymentMethod::CREDIT_CARD,
            PaymentMethod::BANK_TRANSFER,
            PaymentMethod::DIRECT_DEBIT,
            PaymentMethod::WECHAT,
            PaymentMethod::LINE,
            PaymentMethod::ALIPAY
        ];

        if (in_array($paymentMethod, $validValues)) {
            $this->paymentMethod = $this->limitLength($paymentMethod, 32);
        } else {
            Utils::error("Invalid paymentMethod provided: " . $paymentMethod);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentNetwork()
    {
        return $this->paymentNetwork;
    }

    /**
     * The payment netxork to use for the payment.
     * This can be needed for payment methods other than credit card where several
     * networks are available and can not be all used in every countries.
     *
     * @param string $paymentNetwork
     * @return PaymentPrepareRequest
     */
    public function setPaymentNetwork($paymentNetwork)
    {
        $validValues = [
            PaymentNetwork::SOFORT,
            PaymentNetwork::PRZELEWY24,
            PaymentNetwork::IDEAL,
            PaymentNetwork::GIROPAY,
            PaymentNetwork::EPS,
            PaymentNetwork::POLI,
            PaymentNetwork::DRAGONPAY,
            PaymentNetwork::TRUSTLY
        ];

        if (in_array($paymentNetwork, $validValues)) {
            $this->paymentNetwork = $this->limitLength($paymentNetwork, 32);
        } else {
            Utils::error("Invalid paymentNetwork provided: " . $paymentNetwork);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Can be authorize or sale (default value is according to what is configured
     * for the account).
     * This will change the operation done for the payment page.
     * Only relevant for Credit Card payment type.
     * @param string $operation
     * @return PaymentPrepareRequest
     */
    public function setOperation($operation)
    {
        $validValues = [
            OperationType::SALE,
            OperationType::AUTHORIZE
        ];

        if (in_array($operation, $validValues)) {
            $this->operation = $this->limitLength($operation, 32);
        } else {
            Utils::error("Invalid operation provided: " . $operation);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getSecure3d()
    {
        return $this->secure3d;
    }

    /**
     * Force the transaction to use Secure 3D
     *
     * @param bool $secure3d
     * @return PaymentPrepareRequest
     */
    public function setSecure3d($secure3d)
    {
        $this->secure3d = $secure3d;
        return $this;
    }

    /**
     * @return int
     */
    public function getOfferID()
    {
        return $this->offerID;
    }

    /**
     * Predefined price point with initial and rebill period (for Recurrent,
     * InstalmentsPayments payment types)
     *
     * @param int $offerID
     * @return PaymentPrepareRequest
     */
    public function setOfferID($offerID)
    {
        if (C2PValidate::isInt($offerID)) {
            $this->offerID = (int) $offerID;
        } else {
            Utils::error("Bad value for offerID: " . $offerID);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscriptionType()
    {
        return $this->subscriptionType;
    }

    /**
     * Type of subscription.
     *
     * @param string $subscriptionType
     * @return PaymentPrepareRequest
     */
    public function setSubscriptionType($subscriptionType)
    {
        $validValues = [
            SubscriptionType::NORMAL,
            SubscriptionType::PARTPAYMENT,
            SubscriptionType::INFINITE,
            SubscriptionType::ONETIME,
            SubscriptionType::LIFETIME,
        ];

        if (in_array($subscriptionType, $validValues)) {
            $this->subscriptionType = $this->limitLength($subscriptionType, 32);
        } else {
            Utils::error("Invalid subscriptionType provided: " . $subscriptionType);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTrialPeriod()
    {
        return $this->trialPeriod;
    }

    /**
     * Number of days in the initial period (for Recurrent, InstalmentsPayments
     * payment types)
     *
     * @param string $trialPeriod
     * @return PaymentPrepareRequest
     */
    public function setTrialPeriod($trialPeriod)
    {
        $this->trialPeriod = $this->limitLength($trialPeriod, 10);
        return $this;
    }

    /**
     * @return int
     */
    public function getRebillAmount()
    {
        return $this->rebillAmount;
    }

    /**
     * Number in minor unit, amount to be rebilled after the initial period (for
     * Recurrent, InstalmentsPayments payment types)
     *
     * @param int $rebillAmount
     * @return PaymentPrepareRequest
     */
    public function setRebillAmount($rebillAmount)
    {
        if (C2PValidate::isInt($rebillAmount)) {
            $this->rebillAmount = (int) $rebillAmount;
        } else {
            Utils::error("Bad value for rebillAmount: " . $rebillAmount);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRebillPeriod()
    {
        return $this->rebillPeriod;
    }

    /**
     * Number of days next re-billing transaction will be settled in (for
     * Recurrent, InstalmentsPayments payment types)
     *
     * @param string $rebillPeriod
     * @return PaymentPrepareRequest
     */
    public function setRebillPeriod($rebillPeriod)
    {
        $this->rebillPeriod = $this->limitLength($rebillPeriod, 10);
        return $this;
    }

    /**
     * @return int
     */
    public function getRebillMaxIteration()
    {
        return $this->rebillMaxIteration;
    }

    /**
     * Number of re-billing transactions that will be settled (for Recurrent,
     * InstalmentsPayments payment types)
     *
     * @param int $rebillMaxIteration
     * @return PaymentPrepareRequest
     */
    public function setRebillMaxIteration($rebillMaxIteration)
    {
        if (C2PValidate::isInt($rebillMaxIteration)) {
            $this->rebillMaxIteration = (int) $rebillMaxIteration;
        } else {
            Utils::error("Bad value for rebillMaxIteration: " . $rebillMaxIteration);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCtrlRedirectURL()
    {
        return $this->ctrlRedirectURL;
    }

    /**
     * The URL where to redirect the customer after the transaction processing
     *
     * @param string $ctrlRedirectURL
     * @return PaymentPrepareRequest
     */
    public function setCtrlRedirectURL($ctrlRedirectURL)
    {
        if (C2PValidate::isAbsoluteUrl($ctrlRedirectURL)) {
            $this->ctrlRedirectURL = $this->limitLength($ctrlRedirectURL, 2048);
        } else {
            Utils::error("Bad value for ctrlRedirectURL: " . $ctrlRedirectURL);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCtrlCallbackURL()
    {
        return $this->ctrlCallbackURL;
    }

    /**
     * A URL that will be notified of the status of the transaction
     *
     * @param string $ctrlCallbackURL
     * @return PaymentPrepareRequest
     */
    public function setCtrlCallbackURL($ctrlCallbackURL)
    {
        if (C2PValidate::isAbsoluteUrl($ctrlCallbackURL)) {
            $this->ctrlCallbackURL = $this->limitLength($ctrlCallbackURL, 2048);
        } else {
            Utils::error("Bad value for ctrlCallbackURL: " . $ctrlCallbackURL);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCtrlCustomData()
    {
        return $this->ctrlCustomData;
    }

    /**
     * Custom data that will be returned back with the status of the transaction
     *
     * @param string $ctrlCustomData
     * @return PaymentPrepareRequest
     */
    public function setCtrlCustomData($ctrlCustomData)
    {
        $this->ctrlCustomData = $this->limitLength($ctrlCustomData, 2048);
        return $this;
    }

    /**
     * @return int
     */
    public function getThemeID()
    {
        return $this->themeID;
    }

    /**
     * Select a predefined payment page template
     *
     * @param int $themeID
     * @return PaymentPrepareRequest
     */
    public function setThemeID($themeID)
    {
        if (C2PValidate::isInt($themeID)) {
            $this->themeID = (int) $themeID;
        } else {
            Utils::error("Bad value for themeID: " . $themeID);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * Validity for the payment link in ISO 8601 duration format.
     * See http://en.wikipedia.org/wiki/ISO_8601.
     * For example: 2 days => P2D, 1 month => P1M
     *
     * @param string $timeOut
     * @return PaymentPrepareRequest
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $this->limitLength($timeOut, 10);
        return $this;
    }

    /**
     * @return bool
     */
    public function getMerchantNotification()
    {
        return $this->merchantNotification;
    }

    /**
     * Whether or not to send notification to the merchant after payment
     * processing
     *
     * @param bool $merchantNotification
     * @return PaymentPrepareRequest
     */
    public function setMerchantNotification($merchantNotification)
    {
        if (C2PValidate::isBool($merchantNotification)) {
            $this->merchantNotification = $merchantNotification;
        } else {
            Utils::error("Bad value for merchantNotification: " . $merchantNotification);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantNotificationTo()
    {
        return $this->merchantNotificationTo;
    }

    /**
     * Mail address to send merchant notification to
     *
     * @param string $merchantNotificationTo
     * @return PaymentPrepareRequest
     */
    public function setMerchantNotificationTo($merchantNotificationTo)
    {
        if (C2PValidate::isEmail($merchantNotificationTo)) {
            $this->merchantNotificationTo = $this->limitLength($merchantNotificationTo, 100);
        } else {
            Utils::error("Bad value for merchantNotificationTo: " . $merchantNotificationTo);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantNotificationLang()
    {
        return $this->merchantNotificationLang;
    }

    /**
     * Lang to use in merchant notification (defaults to the customer lang)
     *
     * @param string $merchantNotificationLang
     * @return PaymentPrepareRequest
     */
    public function setMerchantNotificationLang($merchantNotificationLang)
    {
        $this->merchantNotificationLang = $this->limitLength($merchantNotificationLang, 2);
        return $this;
    }

    /**
     * @return Shopper
     */
    public function getShopper()
    {
        return $this->shopper;
    }

    /**
     * @param Shopper $shopper
     * @return PaymentPrepareRequest
     */
    public function setShopper($shopper)
    {
        $this->shopper = $shopper;
        return $this;
    }

    /**
     * @return Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     * @return PaymentPrepareRequest
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return PaymentPrepareRequest
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
}