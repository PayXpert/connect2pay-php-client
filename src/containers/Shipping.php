<?php

namespace PayXpert\Connect2Pay\containers;

class Shipping extends Container
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $address1;

    /**
     * @var string
     */
    private $address2;

    /**
     * @var string
     */
    private $address3;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $phone;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of the shipping recipient
     *
     * @param string $name
     * @return Shipping
     */
    public function setName($name)
    {
        $this->name = $this->limitLength($name, 35);
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return Shipping
     */
    public function setCompany($company)
    {
        $this->company = $this->limitLength($company, 128);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     * @return Shipping
     */
    public function setAddress1($address1)
    {
        $this->address1 = $this->limitLength($address1, 50);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     * @return Shipping
     */
    public function setAddress2($address2)
    {
        $this->address2 = $this->limitLength($address2, 50);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     * @return Shipping
     */
    public function setAddress3($address3)
    {
        $this->address3 = $this->limitLength($address3, 50);
        return $this;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     * @return Shipping
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $this->limitLength($zipcode, 16);
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Shipping
     */
    public function setCity($city)
    {
        $this->city = $this->limitLength($city, 50);
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Shipping
     */
    public function setState($state)
    {
        $this->state = $this->limitLength($state, 3);
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return Shipping
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $this->limitLength($countryCode, 2);
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Shipping
     */
    public function setPhone($phone)
    {
        $this->phone = $this->limitLength($phone, 20);
        return $this;
    }
}