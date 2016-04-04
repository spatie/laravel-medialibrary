<?php

namespace Spatie\MediaLibrary\Conversion;

use Spatie\MediaLibrary\Exceptions\InvalidConversionParameter;

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

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name)
    {
        return new static($name);
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the manipulations of this conversion.
     */
    public function getManipulations() : array
    {
        $manipulations = $this->manipulations;

        //if format not is specified, create a jpg
        if (count($manipulations) && !$this->containsFormatManipulation($manipulations)) {
            $manipulations[0]['fm'] = 'jpg';
        };

        return $manipulations;
    }

    /**
     * Set the manipulations for this conversion.
     *
     * @param $manipulations
     * @return $this
     */
    public function setManipulations(...$manipulations)
    {
        $this->manipulations = $manipulations;

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
     * @param array $collectionNames
     *
     * @return $this
     */
    public function performOnCollections(...$collectionNames)
    {
        $this->performOnCollections = $collectionNames;

        return $this;
    }

    /*
     * Determine if this conversion should be performed on the given
     * collection.
     */
    public function shouldBePerformedOn(string $collectionName) : bool
    {
        //if no collections were specified, perform conversion on all collections
        if (!count($this->performOnCollections)) {
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

    /*
     * Determine if the conversion should be queued.
     */
    public function shouldBeQueued() : bool
    {
        return $this->performOnQueue;
    }

    /*
     * Get the extension that the result of this conversion must have.
     */
    public function getResultExtension(string $originalFileExtension = '') : string
    {
        return array_reduce($this->getManipulations(), function ($carry, array $manipulation) {

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

    /**
     * Set the target width.
     * Matches with Glide's 'w'-parameter.
     *
     * @param int $width
     *
     * @return $this
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
     */
    public function setWidth(int $width)
    {
        if (!is_numeric($width) || $width < 1) {
            throw InvalidConversionParameter::invalidWidth();
        }

        $this->setManipulationParameter('w', $width);

        return $this;
    }

    /**
     * Set the target height.
     * Matches with Glide's 'h'-parameter.
     *
     * @param int $height
     *
     * @return $this
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
     */
    public function setHeight(int $height)
    {
        if (!is_numeric($height) || $height < 1) {
            throw InvalidConversionParameter::invalidHeight();
        }

        $this->setManipulationParameter('h', $height);

        return $this;
    }

    /**
     * Set the target format.
     * Matches with Glide's 'fm'-parameter.
     *
     * @param string $format
     *
     * @return $this
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
     */
    public function setFormat(string $format)
    {
        $validFormats = ['jpg', 'png', 'gif'];

        if (!in_array($format, $validFormats)) {
            throw InvalidConversionParameter::invalidFormat($format, $validFormats);
        }

        $this->setManipulationParameter('fm', $format);

        return $this;
    }

    /**
     * Set the target fit.
     * Matches with Glide's 'fit'-parameter.
     *
     * @param string $fit
     *
     * @return $this
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversionParameter
     */
    public function setFit(string $fit)
    {
        $validFits = ['contain', 'max', 'fill', 'stretch', 'crop'];

        if (!in_array($fit, $validFits)) {
            throw InvalidConversionParameter::invalidFit($fit, $validFits);
        }

        $this->setManipulationParameter('fit', $fit);

        return $this;
    }

    /**
     * Crops the image to specific dimensions prior to any other resize operations.
     *
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     *
     * @return $this
     *
     * @throws InvalidConversionParameter
     */
    public function setCrop(int $width, int $height, int $x, int $y)
    {
        foreach (compact('width', 'height', 'x', 'y') as $name => $value) {
            if (!is_numeric($value)) {
                throw InvalidConversionParameter::shouldBeNumeric($name, $value);
            }
        }

        foreach (compact('width', 'height') as $name => $value) {
            if ($value < 1) {
                throw InvalidConversionParameter::shouldBeGreaterThanOne($name, $value);
            }
        }

        $this->setManipulationParameter('crop', implode(',', [$width, $height, $x, $y]));

        return $this;
    }

    /**
     * Set the manipulation parameter.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setManipulationParameter(string $name, string $value)
    {
        if (count($this->manipulations) == 0) {
            $this->manipulations[0] = [];
        };

        $lastIndex = count($this->manipulations) - 1;

        if (!isset($this->manipulations[$lastIndex])) {
            $this->manipulations[$lastIndex] = [];
        }

        $this->manipulations[$lastIndex] = array_merge($this->manipulations[$lastIndex], [$name => $value]);

        return $this;
    }
}
