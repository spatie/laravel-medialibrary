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
     */
    public function saveOrder(array $mediaIds)
    {
        $mediaClass = config('laravel-medialibrary.media_model');
        $mediaClass::setNewOrder($mediaIds);

        return $this;
    }
}
