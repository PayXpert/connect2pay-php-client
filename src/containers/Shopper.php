<?php

namespace PayXpert\Connect2Pay\containers;

use PayXpert\Connect2Pay\helpers\C2PValidate;
use PayXpert\Connect2Pay\helpers\Utils;

class Shopper extends Container
{
    /**
     * @var String
     */
    private $id;

    /**
     * @var String
     */
    private $firstName;

    /**
     * @var String
     */
    private $lastName;

    /**
     * @var String
     */
    private $address1;

    /**
     * @var String
     */
    private $address2;

    /**
     * @var String
     */
    private $address3;

    /**
     * @var String
     */
    private $zipcode;

    /**
     * @var String
     */
    private $city;

    /**
     * @var String
     */
    private $state;

    /**
     * @var String
     */
    private $countryCode;

    /**
     * @var String
     */
    private $homePhonePrefix;

    /**
     * @var String
     */
    private $homePhone;

    /**
     * @var String
     */
    private $mobilePhonePrefix;

    /**
     * @var String
     */
    private $mobilePhone;

    /**
     * @var String
     */
    private $workPhonePrefix;

    /**
     * @var String
     */
    private $workPhone;

    /**
     * @var String
     */
    private $email;

    /**
     * @var String
     */
    private $company;

    /**
     * @var string
     */
    private $birthDate;

    /**
     * @var string
     */
    private $idNumber;

    /**
     * @var String
     */
    private $ipAddress;

    /**
     * @var Account
     */
    private $account;

    /**
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Unique identifier of the shopper at the merchant side
     *
     * @param String $id
     * @return Shopper
     */
    public function setId($id)
    {
        $this->id = $this->limitLength($id, 32);
        return $this;
    }

    /**
     * @return String
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Firstname provided by the shopper
     *
     * @param String $firstName
     * @return Shopper
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $this->limitLength($firstName, 35);
        return $this;
    }

    /**
     * @return String
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Lastname provided by the shopper
     *
     * @param String $lastName
     * @return Shopper
     */
    public function setLastName($lastName)
    {
        $this->lastName = $this->limitLength($lastName, 35);
        return $this;
    }

    /**
     * @return String
     * @deprecated Use getAddress1() instead
     */
    public function getAddress()
    {
        return $this->address1;
    }

    /**
     * @return String
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Address provided by the shopper
     *
     * @param String $address1
     * @return Shopper
     */
    public function setAddress1($address1)
    {
        $this->address1 = $this->limitLength($address1, 50);
        return $this;
    }

    /**
     * @return String
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Address provided by the shopper
     *
     * @param String $address2
     * @return Shopper
     */
    public function setAddress2($address2)
    {
        $this->address2 = $this->limitLength($address2, 50);
        return $this;
    }

    /**
     * @return String
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * Address provided by the shopper
     *
     * @param String $address3
     * @return Shopper
     */
    public function setAddress3($address3)
    {
        $this->address3 = $this->limitLength($address3, 50);
        return $this;
    }

    /**
     * @return String
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Zipcode provided by the shopper.
     *
     * @param String $zipcode
     * @return Shopper
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $this->limitLength($zipcode, 16);
        return $this;
    }

    /**
     * @return String
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * City provided by the shopper.
     *
     * @param String $city
     * @return Shopper
     */
    public function setCity($city)
    {
        $this->city = $this->limitLength($city, 50);
        return $this;
    }

    /**
     * @return String
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * State provided by the shopper
     *
     * @param String $state
     * @return Shopper
     */
    public function setState($state)
    {
        $this->state = $this->limitLength($state, 3);
        return $this;
    }

    /**
     * @return String
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Country provided by the shopper.
     *
     * @param String $countryCode
     * @return Shopper
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $this->limitLength($countryCode, 2);
        return $this;
    }

    /**
     * @return String
     */
    public function getHomePhonePrefix()
    {
        return $this->homePhonePrefix;
    }

    /**
     * Home phone prefix provided by the shopper
     *
     * @param String $homePhonePrefix
     * @return Shopper
     */
    public function setHomePhonePrefix($homePhonePrefix)
    {
        $this->homePhonePrefix = $this->limitLength($homePhonePrefix, 2);
        return $this;
    }

    /**
     * @return String
     */
    public function getHomePhone()
    {
        return $this->homePhone;
    }

    /**
     * Home phone provided by the shopper
     *
     * @param String $homePhone
     * @return Shopper
     */
    public function setHomePhone($homePhone)
    {
        $this->homePhone = $this->limitLength($homePhone, 20);
        return $this;
    }

    /**
     * @return String
     */
    public function getMobilePhonePrefix()
    {
        return $this->mobilePhonePrefix;
    }

    /**
     * Mobile phone prefix provided by the shopper
     *
     * @param String $mobilePhonePrefix
     * @return Shopper
     */
    public function setMobilePhonePrefix($mobilePhonePrefix)
    {
        $this->mobilePhonePrefix = $this->limitLength($mobilePhonePrefix, 2);
        return $this;
    }

    /**
     * @return String
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * Mobile phone provided by the shopper
     *
     * @param String $mobilePhone
     * @return Shopper
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $this->limitLength($mobilePhone, 20);
        return $this;
    }

    /**
     * @return String
     */
    public function getWorkPhonePrefix()
    {
        return $this->workPhonePrefix;
    }

    /**
     * Work phone prefix provided by the shopper
     *
     * @param String $workPhonePrefix
     * @return Shopper
     */
    public function setWorkPhonePrefix($workPhonePrefix)
    {
        $this->workPhonePrefix = $this->limitLength($workPhonePrefix, 2);
        return $this;
    }

    /**
     * @return String
     */
    public function getWorkPhone()
    {
        return $this->workPhone;
    }

    /**
     * Work phone provided by the shopper
     *
     * @param String $workPhone
     * @return Shopper
     */
    public function setWorkPhone($workPhone)
    {
        $this->workPhone = $this->limitLength($workPhone, 20);
        return $this;
    }

    /**
     * @return String
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Email address provided by the shopper.
     *
     * @param String $email
     * @return Shopper
     */
    public function setEmail($email)
    {
        if (C2PValidate::isEmail($email) || $email == 'NA') {
            $this->email = $this->limitLength($email, 100);
        } else {
            Utils::error("Invalid shopper.email provided: " . $email);
        }
        return $this;
    }

    /**
     * @return String
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Company provided by the shopper.
     *
     * @param String $company
     * @return Shopper
     */
    public function setCompany($company)
    {
        $this->company = $this->limitLength($company, 128);
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Birth date provided by the shopper (YYYYMMDD)
     *
     * @param string $birthDate
     * @return Shopper
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $this->limitLength($birthDate, 8);
        return $this;
    }

    /**
     * @return string
     */
    public function getIdNumber()
    {
        return $this->idNumber;
    }

    /**
     * ID number provided by the shopper (identity card, passport...)
     *
     * @param string $idNumber
     * @return Shopper
     */
    public function setIdNumber($idNumber)
    {
        $this->idNumber = $this->limitLength($idNumber, 32);
        return $this;
    }

    /**
     * Should not be used, only for internal use.
     *
     * @param String $ipAddress
     * @return Shopper
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * IP address of the shopper
     *
     * @return String
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Shopper's account details
     *
     * @param Account $account
     * @return Shopper
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }
}