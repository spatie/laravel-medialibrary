<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Collection;

class MediaCollection extends Collection
{
    /**
     *  Delete all media in this collection.
     *
     * @return this
     */
    public function flush()
    {
        $this->map(function (Media $media) {
            $media->delete();
        });

        $this->items = [];

        return $this;
    }

    /**
     * Re order the media in the collection.
     *
     * @param array $mediaIds
     *
     * @return $this
     *
     * @throws \Spatie\EloquentSortable\SortableException
     */
    public function saveOrder(array $mediaIds)
    {
        Media::setNewOrder($mediaIds);

        return $this;
    }
}
