<?php

namespace Spatie\MediaLibrary\Conversion;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Media;

class ConversionCollection extends Collection
{
    /**
     * @var \Spatie\MediaLibrary\Conversion\Media
     */
    protected $media;

    /**
     * @param Media $media
     * @return $this
     */
    public function setMedia(Media $media)
    {

        $this->media = $media;

        $this->items = [];

        $this->addConversionsFromModel();

        $this->addManipulationsFromDb();

        return $this;
    }

    protected function getConversionsFromModel()
    {
        $this->items = $this->media->model->getMediaConversions();
    }

    protected function addManipulationsFromDb()
    {
        foreach ($this->media->manipulations as $collectionName => $manipulation) {

            $this->filter(function (Conversion $conversion) use ($collectionName) {
                return $conversion->shouldBePerformedOn($collectionName);
            })
                ->map(function (Conversion $conversion) use ($manipulation) {
                    $conversion->addAsFirstManipulation($manipulation);
                });
        }
    }


    public function getConversionsForCollection($collectionName)
    {
        return $this->filter(function(Conversion $conversion) use ($collectionName) {
            return $conversion->shouldBePerformedOn($collectionName);
        });
    }

    public function getConversions($collectionName = '')
    {
        if ($collectionName == '') {
            return $this;
        }

        return $this->filter(function(Conversion $conversion) use($collectionName) {
            return $conversion->shouldBePerformedOn($collectionName);
        });
    }








}