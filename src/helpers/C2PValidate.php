<?php

namespace PayXpert\Connect2Pay\helpers;

use PayXpert\Connect2Pay\Connect2PayClient;

/**
 * Validation class
 */
class C2PValidate
{

    /**
     * Check for e-mail validity
     *
     * @param string $email
     *          e-mail address to validate
     * @return boolean Validity is ok or not
     */
    static public function isEmail($email)
    {
        return C2PValidate::isEmpty($email) or
            preg_match('/^[a-z0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$/ui', $email);
    }

    /**
     * Check for date format
     *
     * @param string $date
     *          Date to validate
     * @return boolean Validity is ok or not
     */
    static public function isDateFormat($date)
    {
        return (bool)preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[1-9])|([0-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
    }

    /**
     * Check for date validity
     *
     * @param string $date
     *          Date to validate
     * @return boolean Validity is ok or not
     */
    static public function isDate($date)
    {
        if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches))
            return false;
        return checkdate((int)$matches[2], (int)$matches[5], (int)$matches[0]);
    }

    /**
     * Check for boolean validity
     *
     * @param boolean $bool
     *          Boolean to validate
     * @return boolean Validity is ok or not
     */
    static public function isBool($bool)
    {
        return is_null($bool) || is_bool($bool);
    }

    /**
     * Check for phone number validity
     *
     * @param string $phoneNumber
     *          Phone number to validate
     * @return boolean Validity is ok or not
     */
    static public function isPhoneNumber($phoneNumber)
    {
        return preg_match('/^[+0-9. ()-;]*$/', $phoneNumber);
    }

    /**
     * Check for postal code validity
     *
     * @param string $postcode
     *          Postal code to validate
     * @return boolean Validity is ok or not
     */
    static public function isPostCode($postcode)
    {
        return empty($postcode) or preg_match('/^[a-zA-Z 0-9-]+$/', $postcode);
    }

    /**
     * Check for zip code format validity
     *
     * @param string $zip_code
     *          zip code format to validate
     * @return boolean Validity is ok or not
     */
    static public function isZipCodeFormat($zip_code)
    {
        if (!empty($zip_code))
            return preg_match('/^[NLCnlc -]+$/', $zip_code);
        return true;
    }

    /**
     * Check for an integer validity
     *
     * @param integer $value
     *          Integer to validate
     * @return boolean Validity is ok or not
     */
    static public function isInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Check for an integer validity (unsigned)
     *
     * @param integer $id
     *          Integer to validate
     * @return boolean Validity is ok or not
     */
    static public function isUnsignedInt($value)
    {
        return (preg_match('#^[0-9]+$#', (string)$value) and $value < 4294967296 and $value >= 0);
    }

    /**
     * Check for an numeric string validity (unsigned)
     *
     * @param string $value
     *          Numeric string to validate
     * @return boolean Validity is ok or not
     */
    static public function isNumeric($value)
    {
        return (preg_match('#^[0-9]+$#', (string)$value) === 1);
    }

    /**
     * Check url valdity (disallowed empty string)
     *
     * @param string $url
     *          Url to validate
     * @return boolean Validity is ok or not
     */
    static public function isUrl($url)
    {
        return preg_match('/^[~:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
    }

    /**
     * Check object validity
     *
     * @param integer $object
     *          Object to validate
     * @return boolean Validity is ok or not
     */
    static public function isAbsoluteUrl($url)
    {
        if (!empty($url))
            return preg_match('/^https?:\/\/[:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
        return true;
    }

    /**
     * String validity (PHP one)
     *
     * @param string $data
     *          Data to validate
     * @return boolean Validity is ok or not
     */
    static public function isString($data)
    {
        return is_string($data);
    }

    /**
     * Test if a variable is set
     *
     * @param mixed $field
     * @return boolean field is set or not
     */
    public static function isEmpty($field)
    {
        return ($field === '' or $field === NULL);
    }
}