<?php

namespace Spatie\MediaLibrary;

interface HasMedia extends HasMediaWithoutConversions
{
    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions();
}
