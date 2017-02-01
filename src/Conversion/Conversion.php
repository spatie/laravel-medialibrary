<?php

namespace Spatie\MediaLibrary\Conversion;

use Spatie\Image\Manipulations;

class Conversion
{
    /** @var string */
    protected $name = '';

    /** @var int */
    protected $extractVideoFrameAtSecond = 0;

    /** @var \Spatie\Image\Manipulations */
    protected $manipulations;

    /** @var array */
    protected $performOnCollections = [];

    /** @var bool */
    protected $performOnQueue = true;

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->manipulations = (new Manipulations())->format('jpg');
    }

    public static function create(string $name)
    {
        return new static($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /*
     * Set the timecode in seconds to extract a video thumbnail.
     * Only used on video media.
     */
    public function setExtractVideoFrameAtSecond(int $timecode): Conversion
    {
        $this->extractVideoFrameAtSecond = $timecode;

        return $this;
    }

    public function getExtractVideoFrameAtSecond(): int
    {
        return $this->extractVideoFrameAtSecond;
    }

    public function getManipulations(): Manipulations
    {
        return $this->manipulations;
    }

    /**
     * Set the manipulations for this conversion.
     *
     * @param \Spatie\Image\Manipulations|closure $manipulations
     *
     * @return $this
     */
    public function setManipulations($manipulations)
    {
        if ($manipulations instanceof Manipulations) {
            $this->manipulations = $this->manipulations->mergeManipulations($manipulations);
        }

        if (is_callable($manipulations)) {
            $manipulations($this->manipulations);
        }

        return $this;
    }

    /**
     * Add the given manipulation as the first manipulation.
     *
     * @param \Spatie\Image\Manipulations $manipulations
     *
     * @return $this
     */
    public function addAsFirstManipulation(Manipulations $manipulations)
    {
        $this->manipulations = $manipulations->mergeManipulations($this->manipulations);

        return $this;
    }

    /**
     * Set the collection names on which this conversion must be performed.
     *
     * @param  $collectionNames
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
    public function shouldBePerformedOn(string $collectionName): bool
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

    /*
     * Determine if the conversion should be queued.
     */
    public function shouldBeQueued(): bool
    {
        return $this->performOnQueue;
    }

    /*
     * Get the extension that the result of this conversion must have.
     */
    public function getResultExtension(string $originalFileExtension = ''): string
    {
        if ($manipulationArgument = $this->manipulations->getManipulationArgument('format')) {
            return $manipulationArgument;
        }

        return $originalFileExtension;
    }
}
