<?php

namespace Spatie\MediaLibrary;

class MediaObserver
{
    public function creating(Media $media)
    {
        $media->setHighestOrderNumber();
    }

    public function updating(Media $media)
    {
        $media->hasModifiedManipulations = $media->isDirty('manipulations');

        if ($media->file_name != $media->getOriginal('file_name')) {
            app(Filesystem::class)->renameFile($media, $media->getOriginal('file_name'));
        }
    }

    public function updated(Media $media)
    {
        if (is_null($media->getOriginal('model_id'))) {
            return;
        }

        if ($media->hasModifiedManipulations) {
            app(FileManipulator::class)->createDerivedFiles($media);
        }
    }

    public function deleted(Media $media)
    {
        app(Filesystem::class)->removeFiles($media);
    }
}
