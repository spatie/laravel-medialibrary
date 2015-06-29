<?php

namespace Spatie\MediaLibrary\Conversion;

class Conversion
{
    /**
     * @var string name
     */
    protected $name = '';
    /**
     * @var array
     */
    protected $manipulations = [];

    /**
     * @var array
     */
    protected $performOn = [];

    /**
     * @var bool
     */
    protected $shouldBeQueued = true;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function create($name)
    {
        return new static($name);
    }

    public function getManipulations()
    {
        return $this->manipulations;
    }

    /**
     * @param string $manipulations,...
     * @return $this
     */
    public function setManipulations($manipulations)
    {
        $this->manipulations = func_get_args();

        return $this;
    }

    /**
     * @param array $manipulation
     * @return $this
     */
    public function addAsFirstManipulation(array $manipulation)
    {
        array_unshift($this->manipulations, $manipulation);

        return $this;
    }

    public function performOnCollections($collectionNames)
    {
        $this->performOn = func_get_args();
    }

    public function shouldBePerformedOn($collectionName)
    {
        return in_array($collectionName, $this->performOn);
    }


    public function queued()
    {
        $this->queued = true;

        return $this;
    }

    public function nonQueued()
    {
        $this->queued = false;

        return $this;
    }

    public function shouldBeQueued()
    {
        return $this->shouldBeQueued;
    }




}