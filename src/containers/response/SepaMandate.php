<?php

namespace PayXpert\Connect2Pay\containers\response;

class SepaMandate
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $signatureType;

    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @var integer
     */
    private $signedAt;

    /**
     * @var integer
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $lastUsedAt;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * Description of the mandate
     *
     * @return string The description of the mandate
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the mandate
     *
     * @param
     *          description
     *          The description to set
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Status of the mandate.
     * PENDING_SIGNATURE, AUTOSIGNED, SIGNED, EXPIRED, REVOKED or USED
     *
     * @return string The current status of the mandate
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the current status of the mandate
     *
     * @param
     *          status
     *          The status to set
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * The type of mandate.
     * RECURRENT or ONETIME
     *
     * @return string The type of mandate
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the current mandate type
     *
     * @param
     *          type
     *          The type to set
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * The scheme of the mandate.
     * CORE, COR1 or B2B
     *
     * @return string The scheme of the mandate
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the current scheme of the mandate
     *
     * @param
     *          scheme
     *          The scheme to set
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * The type of signature used to sign the mandate.
     * CHECKBOX, BIOMETRIC or SMS
     *
     * @return string The type of signature used.
     */
    public function getSignatureType()
    {
        return $this->signatureType;
    }

    /**
     * Set the type of signature used to sign the mandate.
     *
     * @param
     *          signatureType
     *          The type to set
     */
    public function setSignatureType($signatureType)
    {
        $this->signatureType = $signatureType;
    }

    /**
     * The phone number used in case the mandate has been signed by SMS.
     *
     * @return string The phone number used for SMS signature
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set the phone number
     *
     * @param
     *          phoneNumber
     *          The phone number to set
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * The date of mandate signature.
     *
     * @return integer The date that mandate was signed at
     */
    public function getSignedAt()
    {
        return $this->signedAt;
    }

    /**
     * The date of mandate signature as DateTime.
     *
     * @return \DateTime The date that mandate was signed at as DateTime object
     */
    public function getSignedAtAsDateTime()
    {
        return $this->getTimestampAsDateTime($this->signedAt);
    }

    /**
     * Set the mandate signing date.
     *
     * @param
     *          signedAt
     *          The signing date to set
     */
    public function setSignedAt($signedAt)
    {
        $this->signedAt = $signedAt;
    }

    /**
     * The date of mandate creation.
     *
     * @return integer The date that mandate was created at
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * The date of mandate creation as DateTime.
     *
     * @return \DateTime The date that mandate was created at as DateTime object
     */
    public function getCreatedAtAsDateTime()
    {
        return $this->getTimestampAsDateTime($this->createdAt);
    }

    /**
     * Set the mandate creation date.
     *
     * @param
     *          createdAt
     *          The creation date to set
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * The date of last mandate use.
     *
     * @return integer The date that mandate was last used at
     */
    public function getLastUsedAt()
    {
        return $this->lastUsedAt;
    }

    /**
     * The date of last mandate use as DateTime.
     *
     * @return \DateTime The date that mandate was last used at as DateTime object
     */
    public function getLastUsedAtAsDateTime()
    {
        return $this->getTimestampAsDateTime($this->lastUsedAt);
    }

    /**
     * Set the mandate last used date.
     *
     * @param
     *          lastUsedAt
     *          The last used date to set
     */
    public function setLastUsedAt($lastUsedAt)
    {
        $this->lastUsedAt = $lastUsedAt;
    }

    /**
     * The URL at which the mandate can be downloaded
     *
     * @return string The download URL of the mandate
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * Set the mandate download URL
     *
     * @param
     *          downloadUrl
     *          The URL to set
     */
    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
    }

    private function getTimestampAsDateTime($timestamp)
    {
        if ($timestamp != null) {
            // API returns date as timestamp in milliseconds
            $timestamp = intval($timestamp / 1000);
            return new \DateTime("@" . $timestamp);
        }

        return null;
    }
}