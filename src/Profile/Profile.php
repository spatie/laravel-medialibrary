<?php

namespace Spatie\MediaLibrary\Profile;

class Profile
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $shouldBeQueued;

    /**
     * @var array
     */
    protected $conversions;

    public function __construct(array $profileArray)
    {
        $this->name = array_keys($profileArray)[0];
        $this->shouldBeQueued = $this->determineIfShouldBeQueued($profileArray);
        $this->conversions = $this->determineConversions($profileArray);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function shouldBeQueued()
    {
        if (! isset($this->profileArray['should_be_queued'])) {
            return true;
        }

        return $this->profileArray['should_be_queued'];
    }

    public function getConversions()
    {
        return $this->conversions;
    }

    /**
     * @param $conversion
     * @return $this
     */
    public function addAsFirstConversion(array $conversion)
    {
        array_unshift($this->conversions, $conversion);

        return $this;
    }

    protected function determineIfShouldBeQueued($profileArray)
    {
        if (! isset($profileArray['should_be_queued'])) {
            return true;
        }

        return $profileArray['should_be_queued'];
    }

    protected function determineConversions($profileArray)
    {
        if (isset($profileArray['conversion'])) {
            return $profileArray['conversion'];
        }

        unset($profileArray['should_be_queued']);

        return $profileArray;
    }

}