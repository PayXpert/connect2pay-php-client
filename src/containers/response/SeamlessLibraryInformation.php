<?php

namespace PayXpert\Connect2Pay\containers\response;

class SeamlessLibraryInformation
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $hash;

    /**
     * The version of the library
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * The sha384 hash of the library to be used in the integrity attribute of the script tag
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }
}