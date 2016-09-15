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
    protected $imageGenerators;

    public function __construct(Media $model)
    {
        $this->model = $model;

        $this->imageGenerators = $this->bindImageGenerators();
    }

    /**
     * Register each drivers in the service container as a singleton.
     */
    private function bindImageGenerators() : Collection
    {
        return $this->model->getImageGenerators()->map(function ($imageGenerator) {
            app()->singleton($imageGenerator);

            return app($imageGenerator);
        })->keyBy(function ($imageGenerator) {
            return $imageGenerator->getMediaType();
        });
    }

    public function getTypeFromExtension(string $extension)
    {

        foreach ($this->imageGenerators as $driver) {
            if (! $driver->fileExtensionIsType($extension)) {
                continue;
            }

            return $driver->getMediaType();
        }
    }

    public function getTypeFromMime(string $mime)
    {
        foreach ($this->imageGenerators as $driver) {
            if (! $driver->fileMimeIsType($mime)) {
                continue;
            }

            return $driver->getMediaType();
        }
    }

    public function getImageGenerators()
    {
        return $this->imageGenerators;
    }

    public function mediaHasDriver(Media $media) : bool
    {
        return $this->imageGenerators->has($media->type) && $this->imageGenerators->get($media->type)->hasRequirements();
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\ImageGenerator\ImageGenerator|null
     */
    public function getDriverForMedia(Media $media)
    {
        return $this->imageGenerators->get($media->type);
    }
}
