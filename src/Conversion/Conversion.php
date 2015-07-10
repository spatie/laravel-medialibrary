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
    protected $performOnCollections = [];

    /**
     * @var bool
     */
    protected $performOnQueue = true;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function create($name)
    {
        return new static($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the manipulations of this conversion.
     *
     * @return array
     */
    public function getManipulations()
    {
        return $this->manipulations;
    }

    /**
     * Set the manipulations for this conversion.
     *
     * @param string $manipulations,...
     *
     * @return $this
     */
    public function setManipulations($manipulations)
    {
        $this->manipulations = func_get_args();

        //if format not is specified, create a jpg
        if (count($this->manipulations) && !$this->containsFormatManipulation($this->manipulations)) {
            $this->manipulations[0]['fm'] = 'jpg';
        };

        return $this;
    }

    /**
     * Add the given manipulation as the first manipulation.
     *
     * @param array $manipulation
     *
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
     * @param string $collectionNames,...
     *
     * @return $this
     */
    public function performOnCollections($collectionNames)
    {
        $this->performOnCollections = func_get_args();

        return $this;
    }

    /**
     * Determine if this conversion should be performed on the given
     * collection.
     *
     * @param string $collectionName
     *
     * @return bool
     */
    public function shouldBePerformedOn($collectionName)
    {
        //if no collections were specified, perform conversion on all collections
        if (! count($this->performOnCollections)) {
            return true;
        }

        if (in_array('*', $this->performOnCollections)) {
            return true;
        }

        return in_array($collectionName, $this->performOnCollections);
    }

    /**
     * Mark this conversion as one that should be queued.
     *
     * @return $this
     */
    public function queued()
    {
        $this->performOnQueue = true;

        return $this;
    }

    /**
     * Mark this conversion as one that should not be queued.
     *
     * @return $this
     */
    public function nonQueued()
    {
        $this->performOnQueue = false;

        return $this;
    }

    /**
     * Determine if the conversion should be queued.
     *
     * @return bool
     */
    public function shouldBeQueued()
    {
        return $this->performOnQueue;
    }

    /**
     * Get the extension that the result of this conversion must have.
     *
     * @param string $originalFileExtension
     *
     * @return string
     */
    public function getResultExtension($originalFileExtension = '')
    {
        return array_reduce($this->manipulations, function ($carry, array $manipulation) {

            return isset($manipulation['fm']) ? $manipulation['fm'] : $carry;

        }, $originalFileExtension);
    }

    /**
     * Determine if the given manipulations contain a format manipulation.
     *
     * @param array $manipulations
     *
     * @return mixed
     */
    protected function containsFormatManipulation(array $manipulations)
    {
        return array_reduce($manipulations, function ($carry, array $manipulation) {
            return array_key_exists('fm', $manipulation) ? true : $carry;
        }, false);
    }
}
