<?php

namespace Spatie\MediaLibrary\Conversion;

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Media;
use Spatie\Image\Manipulations;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class ConversionCollection extends Collection
{
    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return static
     */
    public static function createForMedia(Media $media)
    {
        return (new static())->setMedia($media);
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return $this
     */
    public function setMedia(Media $media)
    {
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
    public function getByName(string $name)
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
     * @param \Spatie\MediaLibrary\Media $media
     */
    protected function addConversionsFromRelatedModel(Media $media)
    {
        $modelName = Arr::get(Relation::morphMap(), $media->model_type, $media->model_type);

        /*
         * To prevent an sql query create a new model instead
         * of the using the associated one.
         */
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

        if ($model instanceof HasMediaConversions) {
            $model->registerMediaConversions($media);
        }

        $this->items = $model->mediaConversions;
    }

    /**
     * Add the extra manipulations that are defined on the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    protected function addManipulationsFromDb(Media $media)
    {
        collect($media->manipulations)->each(function ($manipulations, $conversionName) {
            $this->addManipulationToConversion(new Manipulations([$manipulations]), $conversionName);
        });
    }

    /**
     * Get all the conversions in the collection.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function getConversions(string $collectionName = '')
    {
        if ($collectionName === '') {
            return $this;
        }

        return $this->filter->shouldBePerformedOn($collectionName);
    }

    /*
     * Get all the conversions in the collection that should be queued.
     */
    public function getQueuedConversions(string $collectionName = ''): ConversionCollection
    {
        return $this->getConversions($collectionName)->filter->shouldBeQueued();
    }

    /*
     * Add the given manipulation to the conversion with the given name.
     */
    protected function addManipulationToConversion(Manipulations $manipulations, string $conversionName)
    {
        $this->first(function (Conversion $conversion) use ($conversionName) {
            return $conversion->getName() === $conversionName;
        })->addAsFirstManipulations($manipulations);
    }

    /*
     * Get all the conversions in the collection that should not be queued.
     */
    public function getNonQueuedConversions(string $collectionName = ''): ConversionCollection
    {
        return $this->getConversions($collectionName)->reject->shouldBeQueued();
    }

    /**
     * Return the list of conversion files.
     */
    public function getConversionsFiles(string $collectionName = ''): ConversionCollection
    {
        return $this->getConversions($collectionName)->map(function (Conversion $conversion) {
            return "{$conversion->getName()}.{$conversion->getResultExtension()}";
        });
    }
}
