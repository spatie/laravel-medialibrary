<?php

namespace Spatie\MediaLibrary\Profile;

use Spatie\MediaLibrary\Media;

class ProfileCollectionFactory
{
    /**
     * @param \Spatie\MediaLibrary\Media $media
     * @return ProfileCollection
     */
    public static function createForMedia(Media $media)
    {
        return (new ProfileCollection())->setMedia($media);
    }
}