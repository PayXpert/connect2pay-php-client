<?php

namespace PayXpert\Connect2Pay\containers;

class Recurrence extends Container
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $totalIterations;

    /**
     * @var string
     */
    private $expiry;

    /**
     * @var int
     */
    private $frequency;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Recurrence
     */
    public function setType($type)
    {
        $this->type = $this->limitLength($type, 32);
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalIterations()
    {
        return $this->totalIterations;
    }

    /**
     * @param int $totalIterations
     * @return Recurrence
     */
    public function setTotalIterations($totalIterations)
    {
        $this->totalIterations = $totalIterations;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param string $expiry
     * @return Recurrence
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $this->limitLength($expiry, 8);
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return Recurrence
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $this->limitLength($frequency, 4);
        return $this;
    }
}