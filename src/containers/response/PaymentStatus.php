<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\Order;
use PayXpert\Connect2Pay\containers\Shipping;
use PayXpert\Connect2Pay\containers\response\TransactionAttempt;

/**
 * Represent the status of a payment returned by the payment page
 */
class PaymentStatus extends Container
{
    /**
     * @var String
     */
    private $status;

    /**
     * @var String
     */
    private $merchantToken;

    /**
     * @var String
     */
    private $operation;

    /**
     * @var Int
     */
    private $errorCode;

    /**
     * @var String
     */
    private $errorMessage;

    /**
     * @var String
     */
    private $currency;

    /**
     * @var Int
     */
    private $amount;

    /**
     * @var String
     */
    private $ctrlCustomData;

    /**
     * @var array
     */
    private $transactions;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * Status of the payment: "Authorized", "Not authorized", "Expired", "Call
     * failed", "Pending" or "Not processed"
     *
     **/
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
     * The merchant token of this payment
     *
     * @return String
     */
    public function getMerchantToken()
    {
        return $this->merchantToken;
    }

    public function setMerchantToken($merchantToken)
    {
        $this->merchantToken = $merchantToken;
        return $this;
    }

    /**
     * Type of operation for the last transaction done for this payment: Can be
     * sale or authorize.
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
     * Result code of the last transaction done for this payment
     *
     * @return Int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Error message of the last transaction done for this payment
     *
     * @return String
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * The order ID of the payment
     *
     * @return String
     * @deprecated Use getOrder()->getId() instead
     */
    public function getOrderID()
    {
        return isset($this->order) ? $this->order->getId() : null;
    }

    /**
     * @param $orderID
     * @return $this
     * @deprecated Use getOrder()->setId() instead
     */
    public function setOrderID($orderID)
    {
        if ($this->order != null) {
            $this->order->setId($orderID);
        }
        return $this;
    }

    /**
     * Currency for the payment
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
     * Amount of the payment in cents (1.00€ => 100)
     *
     * @return Int
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
     * Custom data provided by merchant at payment creation.
     *
     * @return String
     */
    public function getCtrlCustomData()
    {
        return $this->ctrlCustomData;
    }

    public function setCtrlCustomData($ctrlCustomData)
    {
        $this->ctrlCustomData = $ctrlCustomData;
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
     * @return PaymentStatus
     */
    public function setOrder($order)
    {
        $this->order = $order;
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
     * @return PaymentStatus
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * The list of transactions done to complete this payment
     *
     * @return array
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    /**
     *
     * @return \PayXpert\Connect2Pay\containers\response\TransactionAttempt
     * @deprecated Use getLastInitialTransactionAttempt()
     */
    public function getLastTransactionAttempt()
    {
        return $this->getLastInitialTransactionAttempt();
    }

    /**
     * Return the last initial transaction attempt done for this payment.
     * Only returns sale, authorize or submission. Transactions with a referral
     * are not considered by this method, only initial transactions done by the
     * customer to complete the payment.
     *
     * @return TransactionAttempt The last transaction attempt done for this
     *         payment
     */
    public function getLastInitialTransactionAttempt()
    {
        $lastAttempt = null;

        if (isset($this->transactions) && is_array($this->transactions) && count($this->transactions) > 0) {
            // Return the entry with the highest timestamp with type sale, authorize,
            // or submission
            foreach ($this->transactions as $transaction) {
                if (in_array($transaction->getOperation(), array("sale", "authorize", "submission")) && $transaction->getRefTransactionID() == null) {
                    if ($lastAttempt == null || $lastAttempt->getDate() < $transaction->getDate()) {
                        $lastAttempt = $transaction;
                    }
                }
            }
        }

        return $lastAttempt;
    }

    /**
     * Get the transaction attempt referring to the provided transactionId with
     * the given operation.
     * In case several transactions are found will return the older one. Used for
     * example to retrieve the collection for a submission.
     *
     * @param
     *          refTransactionId The transaction identifier of the referral
     *          (initial) transaction
     * @param
     *          transactionOperation The operation of the transaction to retrieve
     *          (collection, refund...)
     * @return TransactionAttempt The TransactionAttempt found or null if not
     *         found
     */
    public function getReferringTransactionAttempt($refTransactionId, $transactionOperation)
    {
        $attempts = $this->getReferringTransactionAttempts($refTransactionId, $transactionOperation);

        if (is_array($attempts) && count($attempts) > 0) {
            return $attempts[0];
        }

        return null;
    }

    /**
     * Get the transaction attempts referring to the provided transactionId with
     * the given operation.
     *
     * @param
     *          refTransactionId The transaction identifier of the referral
     *          (initial) transaction
     * @param
     *          transactionOperation The operation of the transactions to retrieve
     *          (collection, refund...). If null, all operations are returned.
     *
     * @return array TransactionAttempt A list with the transactions found (sorted
     *         by date, older first) or
     *         an empty list if not found
     */
    public function getReferringTransactionAttempts($refTransactionId, $transactionOperation = null)
    {
        $attempts = array();

        if ($refTransactionId !== null && isset($this->transactions) && is_array($this->transactions) && count($this->transactions) > 0) {
            foreach ($this->transactions as $transaction) {
                if ($refTransactionId === $transaction->getRefTransactionId() &&
                    ($transactionOperation == null || $transactionOperation === $transaction->getOperation())) {
                    $attempts[] = $transaction;
                }
            }

            // Sort the array by transaction date ascending
            if (count($attempts) > 1) {
                usort($attempts,
                    function ($t1, $t2) {
                        $date1 = $t1->getDate();
                        $date2 = $t2->getDate();

                        if ($date1 === null && $date2 !== null) {
                            return -1;
                        }
                        if ($date1 !== null && $date2 === null) {
                            return 1;
                        }

                        return ($date1 === $date2 ? 0 : ($date1 < $date2 ? -1 : 1));
                    });
            }
        }

        return $attempts;
    }

    public static function getFromJson($statusJson)
    {
        $paymentStatus = null;

        if ($statusJson != null && is_object($statusJson)) {
            // Root element, PaymentStatus
            $paymentStatus = new PaymentStatus();
            $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\response\PaymentStatus');
            self::copyScalarProperties($reflector->getProperties(), $statusJson, $paymentStatus);

            // Order
            if (isset($statusJson->order)) {
                $order = new Order();
                $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\Order');
                self::copyScalarProperties($reflector->getProperties(), $statusJson->order, $order);

                $paymentStatus->setOrder($order);
            }

            // Shipping
            if (isset($statusJson->shipping)) {
                $shipping = new Shipping();
                $reflector = new \ReflectionClass('PayXpert\Connect2Pay\containers\Shipping');
                self::copyScalarProperties($reflector->getProperties(), $statusJson->shipping, $shipping);

                $paymentStatus->setShipping($shipping);
            }

            // Transaction attempts
            if (isset($statusJson->transactions) && is_array($statusJson->transactions)) {
                $transactionAttempts = array();

                foreach ($statusJson->transactions as $transaction) {
                    $transactionAttempts[] = TransactionAttempt::getFromJson($transaction);
                }

                $paymentStatus->setTransactions($transactionAttempts);
            }
        }

        return $paymentStatus;
    }
}