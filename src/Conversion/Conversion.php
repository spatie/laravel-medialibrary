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

    /**
     * Set the collection names on which this conversion must be performed.
     *
     * @param $collectionNames
     * @return $this
     */
    public function performOnCollections($collectionNames)
    {
        $this->performOn = func_get_args();

        return $this;
    }

    /**
     * Determine if this conversion should be performed on the given
     * collection.
     *
     * @param $collectionName
     * @return bool
     */
    public function shouldBePerformedOn($collectionName)
    {
        return in_array($collectionName, $this->performOn);
    }


    /**
     * This conversion should be queued.
     *
     * @return $this
     */
    public function queued()
    {
        $this->queued = true;

        return $this;
    }

    /**
     * This conversion should not be queued.
     *
     * @return $this
     */
    public function nonQueued()
    {
        $this->queued = false;

        return $this;
    }

    /**
     * Determine if the conversion should be queued.
     *
     * @return bool
     */
    public function shouldBeQueued()
    {
        return $this->shouldBeQueued;
    }




}