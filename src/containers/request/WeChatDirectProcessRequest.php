<?php

namespace PayXpert\Connect2Pay\containers\request;

use PayXpert\Connect2Pay\containers\Container;

class WeChatDirectProcessRequest extends Container
{
    const MODE_NATIVE = "native";
    const MODE_QUICKPAY = "quickpay";
    const MODE_SDK = "sdk";
    const MODE_MINIPROGRAM = "miniprogram";

    /* ~~ */
    private $apiVersion;
    private $mode;
    private $quickPayCode;
    private $notificationLang;
    private $notificationTimeZone;
    private $openID;

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
        $this->mode = in_array($mode, array(self::MODE_NATIVE, self::MODE_QUICKPAY, self::MODE_SDK, self::MODE_MINIPROGRAM)) ? $mode : self::MODE_NATIVE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuickPayCode()
    {
        return $this->quickPayCode;
    }

    public function setQuickPayCode($quickPayCode)
    {
        $this->quickPayCode = $quickPayCode;
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

    /**
     * @return mixed
     */
    public function getOpenID()
    {
        return $this->openID;
    }

    public function setOpenID($openID)
    {
        $this->openID = $openID;
        return $this;
    }
}