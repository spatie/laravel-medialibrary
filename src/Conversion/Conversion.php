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

    public function getManipulations()
    {
        return $this->manipulations;
    }

    /**
     * @param array $manipulations
     * @return $this
     */
    public function setManipulations($manipulations)
    {
        $this->manipulations = $manipulations;

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

    public function performOn($collectionNames)
    {
        if (! is_array($collectionNames)) {
            $collectionNames = [$collectionNames];
        }

        $this->performOn = $collectionNames;
    }

    public function shouldBePerformedOn($collectionName)
    {
        return in_array($collectionName, $this->performOn);
    }


    public function queued()
    {
        $this->queued = true;
    }

    public function nonQueued()
    {
        $this->queued = false;
    }




    protected function shouldBeQueued()
    {
        return $this->shouldBeQueued;
    }




}