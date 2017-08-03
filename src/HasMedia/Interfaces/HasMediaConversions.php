<?php

namespace Spatie\MediaLibrary\HasMedia\Interfaces;

use Spatie\MediaLibrary\Media;

interface HasMediaConversions extends HasMedia
{
    public function registerMediaConversions(Media $media = null);
}
