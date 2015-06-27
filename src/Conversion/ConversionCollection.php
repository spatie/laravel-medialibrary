<?php

namespace Spatie\MediaLibrary\Conversion;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Media;

class ConversionCollection extends Collection
{
    /**
     * @param Media $media
     * @return $this
     */
    public function setMedia(Media $media)
    {
        $this->items = [];

        $this->addConversionsFromModel($media);

        $this->addManipulationsFromDb($media);

        return $this;
    }

    protected function getConversionsFromModel($media)
    {
        $this->items = $media->model->getMediaConversions();
    }

    protected function addManipulationsFromDb($media)
    {
        foreach ($media->manipulations as $collectionName => $manipulation) {

            $this->filter(function (Conversion $conversion) use ($collectionName) {
                return $conversion->shouldBePerformedOn($collectionName);
            })
            ->map(function (Conversion $conversion) use ($manipulation) {
                $conversion->addAsFirstManipulation($manipulation);
            });
        }
    }

    public function getConversions($collectionName = '')
    {
        if ($collectionName == '') return $this;

        return $this->filter(function(Conversion $conversion) use($collectionName) {
            return $conversion->shouldBePerformedOn($collectionName);
        });
    }

    public function getQueuedConversions($collectionName = '')
    {
        return $this->getConversions($collectionName)->filter(function (Conversion $conversion) {
            return $conversion->shouldBeQueued();
        });
    }

    public function getNonQueuedConversions($collectionName = '')
    {
        return $this->getConversions($collectionName)->filter(function (Conversion $conversion) {
            return ! $conversion->shouldBeQueued();
        });
    }









}