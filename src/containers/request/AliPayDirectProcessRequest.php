<?php

namespace PayXpert\Connect2Pay\containers\request;

use PayXpert\Connect2Pay\containers\Container;

class AliPayDirectProcessRequest extends Container
{
    const MODE_POS = "pos";
    const MODE_APP = "app";
    const MODE_SDK = "sdk";
    const IDENTITY_CODE_TYPE_BARCODE = "barcode";
    const IDENTITY_CODE_TYPE_QRCODE = "qrcode";

    /* ~~ */
    private $apiVersion;
    private $mode;
    private $buyerIdentityCode;
    private $identityCodeType;
    private $notificationLang;
    private $notificationTimeZone;

    /**
     * @return mixed
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = in_array($mode, array(self::MODE_POS, self::MODE_APP, self::MODE_SDK)) ? $mode : self::MODE_NATIVE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBuyerIdentityCode()
    {
        return $this->buyerIdentityCode;
    }

    public function setBuyerIdentityCode($buyerIdentityCode)
    {
        $this->buyerIdentityCode = $buyerIdentityCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentityCodeType()
    {
        return $this->identityCodeType;
    }

    public function setIdentityCodeType($identityCodeType)
    {
        $this->identityCodeType = $identityCodeType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationLang()
    {
        return $this->notificationLang;
    }

    public function setNotificationLang($notificationLang)
    {
        $this->notificationLang = $notificationLang;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationTimeZone()
    {
        return $this->notificationTimeZone;
    }

    public function setNotificationTimeZone($notificationTimeZone)
    {
        $this->notificationTimeZone = $notificationTimeZone;
        return $this;
    }
}