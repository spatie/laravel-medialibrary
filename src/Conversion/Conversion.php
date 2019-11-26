<?php

namespace Spatie\MediaLibrary\Conversion;

use BadMethodCallException;
use Spatie\Image\Manipulations;

/** @mixin \Spatie\Image\Manipulations */
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

    /** @var bool */
    protected $keepOriginalImageFormat = false;

    /** @var bool */
    protected $generateResponsiveImages = false;

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->manipulations = (new Manipulations())
            ->optimize(config('medialibrary.image_optimizers'))
            ->format(Manipulations::FORMAT_JPG);
    }

    public static function create(string $name)
    {
        return new static($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPerformOnCollections(): array
    {
        if (! count($this->performOnCollections)) {
            return ['default'];
        }

        return $this->performOnCollections;
    }

    /*
     * Set the timecode in seconds to extract a video thumbnail.
     * Only used on video media.
     */
    public function extractVideoFrameAtSecond(int $timeCode): self
    {
        $this->extractVideoFrameAtSecond = $timeCode;

        return $this;
    }

    public function getExtractVideoFrameAtSecond(): int
    {
        return $this->extractVideoFrameAtSecond;
    }

    public function keepOriginalImageFormat(): self
    {
        $this->keepOriginalImageFormat = true;

        return $this;
    }

    public function shouldKeepOriginalImageFormat(): bool
    {
        return $this->keepOriginalImageFormat;
    }

    public function getManipulations(): Manipulations
    {
        return $this->manipulations;
    }

    public function removeManipulation(string $manipulationName) : self
    {
        $this->manipulations->removeManipulation($manipulationName);

        return $this;
    }

    public function withoutManipulations() : self
    {
        $this->manipulations = new Manipulations();

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (! method_exists($this->manipulations, $name)) {
            throw new BadMethodCallException("Manipulation `{$name}` does not exist");
        }

        $this->manipulations->$name(...$arguments);

        return $this;
    }

    /**
     * Set the manipulations for this conversion.
     *
     * @param \Spatie\Image\Manipulations|\Closure $manipulations
     *
     * @return $this
     */
    public function setManipulations($manipulations) : self
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
     * Add the given manipulations as the first ones.
     *
     * @param \Spatie\Image\Manipulations $manipulations
     *
     * @return $this
     */
    public function addAsFirstManipulations(Manipulations $manipulations) : self
    {
        $manipulationSequence = $manipulations->getManipulationSequence()->toArray();

        $this->manipulations
            ->getManipulationSequence()
            ->mergeArray($manipulationSequence);

        return $this;
    }

    /**
     * Set the collection names on which this conversion must be performed.
     *
     * @param  $collectionNames
     *
     * @return $this
     */
    public function performOnCollections(...$collectionNames) : self
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
    public function queued() : self
    {
        $this->performOnQueue = true;

        return $this;
    }

    /**
     * Mark this conversion as one that should not be queued.
     *
     * @return $this
     */
    public function nonQueued() : self
    {
        $this->performOnQueue = false;

        return $this;
    }

    /**
     * Avoid optimization of the converted image.
     *
     * @return $this
     */
    public function nonOptimized() : self
    {
        $this->removeManipulation('optimize');

        return $this;
    }

    /**
     * When creating the converted image, responsive images will be created as well.
     */
    public function withResponsiveImages() : self
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    /**
     * Determine if responsive images should be created for this conversion.
     */
    public function shouldGenerateResponsiveImages(): bool
    {
        return $this->generateResponsiveImages;
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
        if ($this->shouldKeepOriginalImageFormat()) {
            if (in_array($originalFileExtension, ['jpg', 'jpeg', 'pjpg', 'png', 'gif'])) {
                return $originalFileExtension;
            }
        }

        if ($manipulationArgument = $this->manipulations->getManipulationArgument('format')) {
            return $manipulationArgument;
        }

        return $originalFileExtension;
    }

    public function getConversionFile(string $file): string
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        $extension = $this->getResultExtension($fileExtension) ?: $fileExtension;

        return "{$fileName}-{$this->getName()}.{$extension}";
    }
}
