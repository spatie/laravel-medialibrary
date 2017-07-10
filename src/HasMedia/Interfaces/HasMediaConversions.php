<?php

namespace Spatie\MediaLibrary\HasMedia\Interfaces;

interface HasMediaConversions extends HasMedia
{
    
    /**
     * Register the conversions that should be performed.
     *
     * @param $media    The Media Model, base for the conversions
     *
     * @return array
     */
    public function registerMediaConversions($media);
}
