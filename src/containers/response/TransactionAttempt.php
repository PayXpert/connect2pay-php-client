<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Account;
use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\response\BankAccount;
use PayXpert\Connect2Pay\containers\response\BankTransferPaymentMeanInfo;
use PayXpert\Connect2Pay\Connect2PayClient;
use PayXpert\Connect2Pay\containers\response\CreditCardPaymentMeanInfo;
use PayXpert\Connect2Pay\containers\response\DirectDebitPaymentMeanInfo;
use PayXpert\Connect2Pay\containers\response\SepaMandate;
use PayXpert\Connect2Pay\containers\Shopper;
use PayXpert\Connect2Pay\containers\response\ToditoCashPaymentMeanInfo;
use PayXpert\Connect2Pay\helpers\Utils;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;

class TransactionAttempt extends Container
{
    /**
     * @var string
     */
    private $paymentID;

    /**
     * @var string
     */
    private $paymentMerchantToken;

    /**
     * @var String
     */
    private $paymentMethod;

    /**
     * @var String
     */
    private $paymentNetwork;

    /**
     * @var String
     */
    private $operation;

    /**
     * @var integer
     */
    private $date;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var integer
     */
    private $refundedAmount;

    /**
     * @var String
     */
    private $currency;

    /**
     * @var String
     */
    private $resultCode;

    /**
     * @var String
     */
    private $resultMessage;

    /**
     * @var String
     */
    private $status;

    /**
     * @var Shopper
     */
    private $shopper;

    /**
     * @var String
     */
    private $transactionID;

    /**
     * @var String
     */
    private $refTransactionID;

    /**
     * @var String
     */
    private $providerTransactionID;

    /**
     * @var Int
     */
    private $subscriptionID;

    /**
     * @var object Depends on the paymentMethod
     */
    private $paymentMeanInfo;

    /**
     * @var String
     */
    private $orderID;

    /**
     * @var String
     */
    private $orderDescription;

    /**
     * Identifier of the payment this transaction belongs to
     *
     * @return string
     */
    public function getPaymentID()
    {
        return $this->paymentID;
    }

    public function setPaymentID($paymentID)
    {
        $this->paymentID = $paymentID;
        return $this;
    }

    /**
     * Merchant Token of the payment this transaction belongs to
     *
     * @return string
     */
    public function getPaymentMerchantToken()
    {
        return $this->paymentMerchantToken;
    }

    public function setPaymentMerchantToken($paymentMerchantToken)
    {
        $this->paymentMerchantToken = $paymentMerchantToken;
        return $this;
    }

    /**
     *
     * @deprecated Use getPaymentMethod()
     */
    public function getPaymentType()
    {
        Utils::deprecation_error('Use getPaymentMethod().');
        return $this->getPaymentMethod();
    }

    /**
     *
     * @deprecated Use setPaymentMethod()
     */
    public function setPaymentType($paymentType)
    {
        Utils::deprecation_error('Use setPaymentMethod().');
        return $this->setPaymentMethod($paymentType);
    }

    /**
     * Method of payment for this transaction attempt: CreditCard, BankTransfer,
     * DirectDebit...
     *
     * @return String
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
     * Payment network used when the payment method is multi networks
     *
     * @return String
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
     * Type of operation for that transaction: sale, authorize, rebill,
     * submission, collection, refund
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

    /**
     * Date of the transaction
     *
     * @return int
     */
    public function getDate()
    {
        return $this->date;
    }

    public function getDateAsDateTime()
    {
        if ($this->date != null) {
            // API returns date as timestamp in milliseconds
            $timestamp = intval($this->date / 1000);
            return new \DateTime("@" . $timestamp);
        }

        return null;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Amount of the transaction
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Amount already refunded for this transaction
     *
     * @return int
     */
    public function getRefundedAmount()
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount($refundedAmount)
    {
        $this->refundedAmount = $refundedAmount;
        return $this;
    }

    /**
     * The currency for the transaction
     *
     * @return String
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * The result code for this transaction
     *
     * @return String
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;
        return $this;
    }

    /**
     * The result message for this transaction
     *
     * @return String
     */
    public function getResultMessage()
    {
        return $this->resultMessage;
    }

    public function setResultMessage($resultMessage)
    {
        $this->resultMessage = $resultMessage;
        return $this;
    }

    /**
     * Status of the transaction: "Authorized", "Not authorized", "Expired", "Call
     * failed", "Pending" or "Not processed"
     *
     * @return String
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Shopper information for this transaction
     *
     * @return Shopper
     */
    public function getShopper()
    {
        return $this->shopper;
    }

    public function setShopper($shopper)
    {
        $this->shopper = $shopper;
        return $this;
    }

    /**
     * Transaction identifier of this transaction.
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
     * Identifier of the transaction this transaction refers to.
     *
     * @return String
     */
    public function getRefTransactionID()
    {
        return $this->refTransactionID;
    }

    public function setRefTransactionID($refTransactionID)
    {
        $this->refTransactionID = $refTransactionID;
        return $this;
    }

    /**
     * Identifier of the transaction at the provider side.
     *
     * @return String
     */
    public function getProviderTransactionID()
    {
        return $this->providerTransactionID;
    }

    public function setProviderTransactionID($providerTransactionID)
    {
        $this->providerTransactionID = $providerTransactionID;
        return $this;
    }

    /**
     *
     * @deprecated Use getPaymentNetwork()
     */
    public function getProvider()
    {
        Utils::deprecation_error('Use getPaymentNetwork().');
        return $this->paymentNetwork;
    }

    /**
     *
     * @deprecated Use setPaymentNetwork()
     */
    public function setProvider($provider)
    {
        Utils::deprecation_error('Use setPaymentNetwork().');
        return $this->setPaymentNetwork($provider);
    }

    /**
     * Identifier of the subscription this transaction is part of (if any).
     *
     * @return Int
     */
    public function getSubscriptionID()
    {
        return $this->subscriptionID;
    }

    public function setSubscriptionID($subscriptionID)
    {
        $this->subscriptionID = $subscriptionID;
        return $this;
    }

    /**
     * Details of the payment mean used to process the transaction
     *
     * @return object Depends on the paymentMethod
     */
    public function getPaymentMeanInfo()
    {
        return $this->paymentMeanInfo;
    }

    public function setPaymentMeanInfo($paymentMeanInfo)
    {
        $this->paymentMeanInfo = $paymentMeanInfo;
        return $this;
    }

    /**
     * The merchant internal unique order identifier as provided during payment creation
     *
     * @return String
     */
    public function getOrderID()
    {
        return $this->orderID;
    }

    public function setOrderID($orderID)
    {
        $this->orderID = $orderID;
        return $this;
    }

    public function setOrderDescription($orderDescription)
    {
        $this->orderDescription = $orderDescription;
        return $this;
    }

    /**
     * The description of the product purchased by the customer as provided during payment creation
     *
     * @return String
     */
    public function getOrderDescription()
    {
        return $this->orderDescription;
    }

    public static function getFromRawJson($json)
    {
        return self::getFromJson(json_decode($json, false));
    }

    public static function getFromJson($transaction)
    {
        $transAttempt = null;

        if ($transaction != null && is_object($transaction)) {
            $transAttempt = new TransactionAttempt();

            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\TransactionAttempt');
            self::copyScalarProperties($reflector->getProperties(), $transaction, $transAttempt);

            // Set the shopper
            if (isset($transaction->shopper) && is_object($transaction->shopper)) {
                $shopper = new Shopper();
                $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\Shopper');
                self::copyScalarProperties($reflector->getProperties(), $transaction->shopper, $shopper);

                // Set the Account
                if (isset($transaction->shopper->account) && is_object($transaction->shopper->account)) {
                    $account = new Account();
                    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\Account');
                    self::copyScalarProperties($reflector->getProperties(), $transaction->shopper->account, $account);
                    $shopper->setAccount($account);
                }

                $transAttempt->setShopper($shopper);
            }

            // Payment Mean Info
            if (isset($transaction->paymentMethod) && isset($transaction->paymentMeanInfo) && is_object($transaction->paymentMeanInfo)) {
                $paymentMeanInfo = null;
                switch ($transaction->paymentMethod) {
                    case PaymentMethod::CREDIT_CARD:
                        $paymentMeanInfo = self::extractCreditCardPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                    case PaymentMethod::TODITO_CASH:
                        $paymentMeanInfo = self::extractToditoCashPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                    case PaymentMethod::BANK_TRANSFER:
                        $paymentMeanInfo = self::extractBankTransferPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                    case PaymentMethod::DIRECT_DEBIT:
                        $paymentMeanInfo = self::extractDirectDebitPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                    case PaymentMethod::WECHAT:
                        $paymentMeanInfo = self::extractWeChatPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                    case PaymentMethod::ALIPAY:
                        $paymentMeanInfo = self::extractAlipayPaymentMeanInfo($transaction->paymentMeanInfo);
                        break;
                }

                if ($paymentMeanInfo !== null) {
                    $transAttempt->setPaymentMeanInfo($paymentMeanInfo);
                }
            }
        }

        return $transAttempt;
    }

    private static function extractCreditCardPaymentMeanInfo($paymentMeanInfo)
    {
        $ccInfo = new CreditCardPaymentMeanInfo();
        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\CreditCardPaymentMeanInfo');
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $ccInfo);

        return $ccInfo;
    }

    private static function extractToditoCashPaymentMeanInfo($paymentMeanInfo)
    {
        $tcInfo = new ToditoCashPaymentMeanInfo();
        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\ToditoCashPaymentMeanInfo');
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $tcInfo);

        return $tcInfo;
    }

    private static function extractBankTransferPaymentMeanInfo($paymentMeanInfo)
    {
        $btInfo = new BankTransferPaymentMeanInfo();
        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\BankAccount');

        if (isset($paymentMeanInfo->sender) && is_object($paymentMeanInfo->sender)) {
            $sender = new BankAccount();
            self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->sender, $sender);
            $btInfo->setSender($sender);
        }

        if (isset($paymentMeanInfo->recipient) && is_object($paymentMeanInfo->recipient)) {
            $recipient = new BankAccount();
            self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->recipient, $recipient);
            $btInfo->setRecipient($recipient);
        }

        return $btInfo;
    }

    private static function extractDirectDebitPaymentMeanInfo($paymentMeanInfo)
    {
        $ddInfo = new DirectDebitPaymentMeanInfo();

        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\DirectDebitPaymentMeanInfo');
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $ddInfo);

        if (is_object($paymentMeanInfo->bankAccount)) {
            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\BankAccount');
            $account = new BankAccount();
            self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->bankAccount, $account);

            if (is_object($paymentMeanInfo->bankAccount->sepaMandate)) {
                $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\SepaMandate');
                $mandate = new SepaMandate();
                self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->bankAccount->sepaMandate, $mandate);
                $account->setSepaMandate($mandate);
            }

            $ddInfo->setBankAccount($account);
        }

        return $ddInfo;
    }

    private static function extractWeChatPaymentMeanInfo($paymentMeanInfo)
    {
        $weChatInfo = new WeChatPaymentMeanInfo();

        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\WeChatPaymentMeanInfo');
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $weChatInfo);

        return $weChatInfo;
    }

    private static function extractAlipayPaymentMeanInfo($paymentMeanInfo)
    {
        $alipayInfo = new AlipayPaymentMeanInfo();

        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\AlipayPaymentMeanInfo');
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $alipayInfo);

        return $alipayInfo;
    }
}