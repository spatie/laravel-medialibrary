<?php

namespace Spatie\MediaLibrary\ImageDrivers;

interface MediaImageDriver
{
    /**
     * The class of the image object that manipulation closures
     * for this driver are typed against.
     *
     * @return class-string
     */
    public function imageClass(): string;
}
