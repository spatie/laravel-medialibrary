<?php

namespace Spatie\Medialibrary\Conversions;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Image\Manipulations;
use Spatie\Medialibrary\Exceptions\InvalidConversion;
use Spatie\Medialibrary\Models\Media;

class ConversionCollection extends Collection
{
    protected Media $media;

    public static function createForMedia(Media $media): self
    {
        return (new static())->setMedia($media);
    }

    public function setMedia(Media $media): self
    {
        $this->media = $media;

        $this->items = [];

        $this->addConversionsFromRelatedModel($media);

        $this->addManipulationsFromDb($media);

        return $this;
    }

    public function getByName(string $name): Conversion
    {
        $conversion = $this->first(fn(Conversion $conversion) => $conversion->getName() === $name);

        if (! $conversion) {
            throw InvalidConversion::unknownName($name);
        }

        return $conversion;
    }

    protected function addConversionsFromRelatedModel(Media $media): void
    {
        $modelName = Arr::get(Relation::morphMap(), $media->model_type, $media->model_type);

        /** @var \Spatie\Medialibrary\HasMedia\HasMedia $model */
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

    public function getQueuedConversions(string $collectionName = ''): self
    {
        return $this->getConversions($collectionName)->filter->shouldBeQueued();
    }


    protected function addManipulationToConversion(Manipulations $manipulations, string $conversionName)
    {
        /** @var \Spatie\Medialibrary\Conversions\Conversion|null $conversion */
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

    public function getNonQueuedConversions(string $collectionName = ''): self
    {
        return $this->getConversions($collectionName)->reject->shouldBeQueued();
    }

    public function getConversionsFiles(string $collectionName = ''): self
    {
        $fileName = pathinfo($this->media->file_name, PATHINFO_FILENAME);

        return $this->getConversions($collectionName)->map(fn(Conversion $conversion) => $conversion->getConversionFile($this->media));
    }
}
