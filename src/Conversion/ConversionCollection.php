<?php

namespace Spatie\MediaLibrary\Conversion;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\Exceptions\UnknownConversion;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Spatie\MediaLibrary\Media;

class ConversionCollection extends Collection
{
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
     *  Get a conversion by it's name;.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UnknownConversion
     */
    public function getByName($name)
    {
        $conversion = $this->first(function ($key, Conversion $conversion) use ($name) {
            return $conversion->getName() == $name;
        });

        if (!$conversion) {
            throw new UnknownConversion("Conversion {$name} is not registered");
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
         * of the using the associated one
         */
        $model = new $modelName();

        if ($model instanceof HasMediaConversions) {
            $model->registerMediaConversions();
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
        foreach ($media->manipulations as $conversionName => $manipulation) {
            $this->addManipulationToConversion($manipulation, $conversionName);
        }
    }

    /**
     * Get all the conversions in the collection.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function getConversions($collectionName = '')
    {
        if ($collectionName == '') {
            return $this;
        }

        return $this->filter(function (Conversion $conversion) use ($collectionName) {
            return $conversion->shouldBePerformedOn($collectionName);
        });
    }

    /**
     * Get all the conversions in the collection that should be queued.
     *
     * @param string $collectionName
     *
     * @return \Spatie\MediaLibrary\Conversion\ConversionCollection
     */
    public function getQueuedConversions($collectionName = '')
    {
        return $this->getConversions($collectionName)->filter(function (Conversion $conversion) {
            return $conversion->shouldBeQueued();
        });
    }

    /**
     * Add the given manipulation to the conversion with the given name.
     *
     * @param array  $manipulation
     * @param string $conversionName
     */
    protected function addManipulationToConversion($manipulation, $conversionName)
    {
        foreach ($this as $conversion) {
            if ($conversion->getName() == $conversionName) {
                $conversion->addAsFirstManipulation($manipulation);

                return;
            }
        }
    }

    /**
     * Get all the conversions in the collection that should not be queued.
     *
     * @param string $collectionName
     *
     * @return ConversionCollection
     */
    public function getNonQueuedConversions($collectionName = '')
    {
        return $this->getConversions($collectionName)->filter(function (Conversion $conversion) {
            return !$conversion->shouldBeQueued();
        });
    }
}
