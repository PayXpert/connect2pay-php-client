<?php

namespace PayXpert\Connect2Pay\helpers;

class Utils
{

    public static function deprecation_error($message)
    {
        trigger_error($message, version_compare(phpversion(), '5.3.0', '<') ? E_USER_NOTICE : E_USER_DEPRECATED);
    }

    public static function error($message)
    {
        trigger_error($message, E_USER_WARNING);
    }

    public static function urlSafeBase64Decode($string)
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }

    public static function pkcs5Unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            // The initial text was empty
            return "";
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            // The length of the padding sequence is incorrect
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }
}