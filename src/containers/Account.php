<?php

namespace PayXpert\Connect2Pay\containers;

use PayXpert\Connect2Pay\helpers\Utils;
use PayXpert\Connect2Pay\containers\constant\AccountAge;
use PayXpert\Connect2Pay\containers\constant\AccountLastChange;
use PayXpert\Connect2Pay\containers\constant\AccountPwChange;
use PayXpert\Connect2Pay\containers\constant\AccountShipInfoAge;
use PayXpert\Connect2Pay\containers\constant\AccountPaymentMeanAge;

class Account extends Container
{
    private $age;

    private $date;

    private $lastChange;

    private $lastChangeDate;

    private $pwChange;

    private $pwChangeDate;

    private $shipInfoAge;

    private $shipInfoDate;

    private $transLastDay;

    private $transLastYear;

    private $cardsAddLastDay;

    private $orderSixMonths;

    private $suspicious;

    private $namesMatching;

    private $paymentMeanAge;

    private $paymentMeanDate;

    /**
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param string $age
     * @return Account
     */
    public function setAge($age)
    {
        $validValues = [
            AccountAge::NO_ACCOUNT,
            AccountAge::DURING_TRANSACTION,
            AccountAge::LESS_30_DAYS,
            AccountAge::BETWEEN_30_60_DAYS,
            AccountAge::MORE_60_DAYS
        ];

        if (in_array($age, $validValues)) {
            $this->age = $this->limitLength($age, 2);
        } else {
            Utils::error("Bad value for account.age: " . $age);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return Account
     */
    public function setDate($date)
    {
        $this->date = $this->limitLength($date, 8);
        return $this;
    }

    /**
     * @return string
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * @param string $lastChange
     * @return Account
     */
    public function setLastChange($lastChange)
    {
        $validValues = [
            AccountLastChange::DURING_TRANSACTION,
            AccountLastChange::LESS_30_DAYS,
            AccountLastChange::BETWEEN_30_60_DAYS,
            AccountLastChange::MORE_60_DAYS
        ];

        if (in_array($lastChange, $validValues)) {
            $this->lastChange = $this->limitLength($lastChange, 2);
        } else {
            Utils::error("Bad value for account.lastChange: " . $lastChange);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLastChangeDate()
    {
        return $this->lastChangeDate;
    }

    /**
     * @param string $lastChangeDate
     * @return Account
     */
    public function setLastChangeDate($lastChangeDate)
    {
        $this->lastChangeDate = $this->limitLength($lastChangeDate, 8);
        return $this;
    }

    /**
     * @return string
     */
    public function getPwChange()
    {
        return $this->pwChange;
    }

    /**
     * @param string $pwChange
     * @return Account
     */
    public function setPwChange($pwChange)
    {
        $validValues = [
            AccountPwChange::NO_CHANGE,
            AccountPwChange::DURING_TRANSACTION,
            AccountPwChange::LESS_30_DAYS,
            AccountPwChange::BETWEEN_30_60_DAYS,
            AccountPwChange::MORE_60_DAYS
        ];

        if (in_array($pwChange, $validValues)) {
            $this->pwChange = $this->limitLength($pwChange, 2);
        } else {
            Utils::error("Bad value for account.pwChange: " . $pwChange);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPwChangeDate()
    {
        return $this->pwChangeDate;
    }

    /**
     * @param string $pwChangeDate
     * @return Account
     */
    public function setPwChangeDate($pwChangeDate)
    {
        $this->pwChangeDate = $this->limitLength($pwChangeDate, 8);
        return $this;
    }

    /**
     * @return string
     */
    public function getShipInfoAge()
    {
        return $this->shipInfoAge;
    }

    /**
     * @param string $shipInfoAge
     * @return Account
     */
    public function setShipInfoAge($shipInfoAge)
    {
        $validValues = [
            AccountShipInfoAge::DURING_TRANSACTION,
            AccountShipInfoAge::LESS_30_DAYS,
            AccountShipInfoAge::BETWEEN_30_60_DAYS,
            AccountShipInfoAge::MORE_60_DAYS
        ];

        if (in_array($shipInfoAge, $validValues)) {
            $this->shipInfoAge = $this->limitLength($shipInfoAge, 2);
        } else {
            Utils::error("Bad value for account.shipInfoAge: " . $shipInfoAge);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getShipInfoDate()
    {
        return $this->shipInfoDate;
    }

    /**
     * @param string $shipInfoDate
     * @return Account
     */
    public function setShipInfoDate($shipInfoDate)
    {
        $this->shipInfoDate = $this->limitLength($shipInfoDate, 8);
        return $this;
    }

    /**
     * @return int
     */
    public function getTransLastDay()
    {
        return $this->transLastDay;
    }

    /**
     * @param int $transLastDay
     * @return Account
     */
    public function setTransLastDay($transLastDay)
    {
        $this->transLastDay = $transLastDay;
        return $this;
    }

    /**
     * @return int
     */
    public function getTransLastYear()
    {
        return $this->transLastYear;
    }

    /**
     * @param int $transLastYear
     * @return Account
     */
    public function setTransLastYear($transLastYear)
    {
        $this->transLastYear = $transLastYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getCardsAddLastDay()
    {
        return $this->cardsAddLastDay;
    }

    /**
     * @param int $cardsAddLastDay
     * @return Account
     */
    public function setCardsAddLastDay($cardsAddLastDay)
    {
        $this->cardsAddLastDay = $cardsAddLastDay;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderSixMonths()
    {
        return $this->orderSixMonths;
    }

    /**
     * @param int $orderSixMonths
     * @return Account
     */
    public function setOrderSixMonths($orderSixMonths)
    {
        $this->orderSixMonths = $orderSixMonths;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuspicious()
    {
        return $this->suspicious;
    }

    /**
     * @param bool $suspicious
     * @return Account
     */
    public function setSuspicious($suspicious)
    {
        $this->suspicious = $suspicious;
        return $this;
    }

    /**
     * @return bool
     */
    public function getNamesMatching()
    {
        return $this->namesMatching;
    }

    /**
     * @param bool $namesMatching
     * @return Account
     */
    public function setNamesMatching($namesMatching)
    {
        $this->namesMatching = $namesMatching;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMeanAge()
    {
        return $this->paymentMeanAge;
    }

    /**
     * @param string $paymentMeanAge
     * @return Account
     */
    public function setPaymentMeanAge($paymentMeanAge)
    {
        $validValues = [
            AccountPaymentMeanAge::NO_ACCOUNT,
            AccountPaymentMeanAge::DURING_TRANSACTION,
            AccountPaymentMeanAge::LESS_30_DAYS,
            AccountPaymentMeanAge::BETWEEN_30_60_DAYS,
            AccountPaymentMeanAge::MORE_60_DAYS
        ];

        if (in_array($paymentMeanAge, $validValues)) {
            $this->paymentMeanAge = $this->limitLength($paymentMeanAge, 2);
        } else {
            Utils::error("Bad value for account.paymentMeanAge: " . $paymentMeanAge);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMeanDate()
    {
        return $this->paymentMeanDate;
    }

    /**
     * @param string $paymentMeanDate
     * @return Account
     */
    public function setPaymentMeanDate($paymentMeanDate)
    {
        $this->paymentMeanDate = $this->limitLength($paymentMeanDate, 8);
        return $this;
    }
}