<?php

namespace Spatie\MediaLibrary;

class MediaObserver
{
    public function updating(Media $media)
    {
        $media->previousManipulations = $media->getOriginal('manipulations');
    }

    public function updated(Media $media)
    {
        if ($media->manipulations != $media->previousManipulations) {
            app(FileManipulator::class)->createDerivedFiles($media);
        }
    }

    public function deleted(Media $media)
    {
        app(Filesystem::class)->removeFiles($media);
    }
}
