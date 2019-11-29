<?php

namespace Spatie\MediaLibrary\Conversion;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Models\Media;

class ConversionCollection extends Collection
{
    /** @var \Spatie\MediaLibrary\Models\Media */
    protected $media;

    /**
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return static
     */
    public static function createForMedia(Media $media)
    {
        return (new static())->setMedia($media);
    }

    /**
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return $this
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;

        $this->items = [];

        $this->addConversionsFromRelatedModel($media);

        $this->addManipulationsFromDb($media);

        return $this;
    }

    /**
     *  Get a conversion by it's name.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getByName(string $name): Conversion
    {
        $conversion = $this->first(function (Conversion $conversion) use ($name) {
            return $conversion->getName() === $name;
        });

        if (! $conversion) {
            throw InvalidConversion::unknownName($name);
        }

        return $conversion;
    }

    /**
     * Add the conversion that are defined on the related model of
     * the given media.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     */
    protected function addConversionsFromRelatedModel(Media $media)
    {
        $modelName = Arr::get(Relation::morphMap(), $media->model_type, $media->model_type);

        /** @var \Spatie\MediaLibrary\HasMedia\HasMedia $model */
        $model = new $modelName();

        /*
         * In some cases the user might want to get the actual model
         * instance so conversion parameters can depend on model
         * properties. This will causes extra queries.
         */
        if ($model->registerMediaConversionsUsingModelInstance) {
            $model = $media->model;

            $model->mediaConversion = [];
        }

        $model->registerAllMediaConversions($media);

        $this->items = $model->mediaConversions;
    }

    /**
     * Add the extra manipulations that are defined on the given media.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     */
    protected function addManipulationsFromDb(Media $media)
    {
        collect($media->manipulations)->each(function ($manipulations, $conversionName) {
            $this->addManipulationToConversion(new Manipulations([$manipulations]), $conversionName);
        });
    }

    public function getConversions(string $collectionName = ''): self
    {
        if ($collectionName === '') {
            return $this;
        }

        return $this->filter->shouldBePerformedOn($collectionName);
    }

    /*
     * Get all the conversions in the collection that should be queued.
     */
    public function getQueuedConversions(string $collectionName = ''): self
    {
        return $this->getConversions($collectionName)->filter->shouldBeQueued();
    }

    /*
     * Add the given manipulation to the conversion with the given name.
     */
    protected function addManipulationToConversion(Manipulations $manipulations, string $conversionName)
    {
        /** @var \Spatie\MediaLibrary\Conversion\Conversion|null $conversion */
        $conversion = $this->first(function (Conversion $conversion) use ($conversionName) {
            if (! in_array($this->media->collection_name, $conversion->getPerformOnCollections())) {
                return false;
            }

            if ($conversion->getName() !== $conversionName) {
                return false;
            }

            return true;
        });

        if ($conversion) {
            $conversion->addAsFirstManipulations($manipulations);
        }

        if ($conversionName === '*') {
            $this->each->addAsFirstManipulations(clone $manipulations);
        }
    }

    /*
     * Get all the conversions in the collection that should not be queued.
     */
    public function getNonQueuedConversions(string $collectionName = ''): self
    {
        return $this->getConversions($collectionName)->reject->shouldBeQueued();
    }

    /*
     * Return the list of conversion files.
     */
    public function getConversionsFiles(string $collectionName = ''): self
    {
        $fileName = pathinfo($this->media->file_name, PATHINFO_FILENAME);

        return $this->getConversions($collectionName)->map(function (Conversion $conversion) use ($fileName) {
            return $conversion->getConversionFile($fileName);
        });
    }
}
