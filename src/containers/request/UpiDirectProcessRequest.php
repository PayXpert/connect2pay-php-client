<?php

namespace PayXpert\Connect2Pay\containers\request;

use PayXpert\Connect2Pay\containers\Container;

class UpiDirectProcessRequest extends Container
{
    const MODE_POS = "pos";
    /* ~~ */
    private $apiVersion;
    private $mode;
    private $notificationLang;
    private $notificationTimeZone;

    /**
     * @return mixed
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion): UpiDirectProcessRequest
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

    public function setMode($mode): UpiDirectProcessRequest
    {
        $this->mode = in_array($mode, array(self::MODE_POS)) ? $mode : self::MODE_POS;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationLang()
    {
        return $this->notificationLang;
    }

    public function setNotificationLang($notificationLang): UpiDirectProcessRequest
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

    public function setNotificationTimeZone($notificationTimeZone): UpiDirectProcessRequest
    {
        $this->notificationTimeZone = $notificationTimeZone;
        return $this;
    }
}