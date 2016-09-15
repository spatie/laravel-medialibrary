<?php

namespace Spatie\MediaLibrary\ImageGenerator;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Media;

class ImageGeneratorHandler
{
    /** @var \Spatie\MediaLibrary\Media */
    protected $model;

    /**
     * ImageGenerator[]|Collection.
     */
    protected $drivers;

    public function __construct(Media $model)
    {
        $this->model = $model;

        $this->drivers = $this->bindDrivers();
    }

    /**
     * Register each drivers in the service container as a singleton.
     */
    private function bindDrivers() : Collection
    {
        return $this->model->getImageGenerators()->map(function ($driver) {
            app()->singleton($driver);

            return app($driver);
        })->keyBy(function ($driver) {
            return $driver->getMediaType();
        });
    }

    public function getTypeFromExtension(string $extension)
    {

        foreach ($this->drivers as $driver) {
            if (! $driver->fileExtensionIsType($extension)) {
                continue;
            }

            return $driver->getMediaType();
        }
    }

    public function getTypeFromMime(string $mime)
    {
        foreach ($this->drivers as $driver) {
            if (! $driver->fileMimeIsType($mime)) {
                continue;
            }

            return $driver->getMediaType();
        }
    }

    public function getDrivers()
    {
        return $this->drivers;
    }

    public function mediaHasDriver(Media $media) : bool
    {
        return $this->drivers->has($media->type) && $this->drivers->get($media->type)->hasRequirements();
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\ImageGenerator\ImageGenerator|null
     */
    public function getDriverForMedia(Media $media)
    {
        return $this->drivers->get($media->type);
    }
}
