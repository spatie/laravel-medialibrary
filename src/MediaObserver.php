<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Models\Media;

class MediaObserver
{
    public function creating(Media $media)
    {
        if ($media->shouldSortWhenCreating()) {
            $media->setHighestOrderNumber();
        }
    }

    public function updating(Media $media)
    {
        if ($media->file_name !== $media->getOriginal('file_name')) {
            app(Filesystem::class)->syncFileNames($media);
        }
    }

    public function updated(Media $media)
    {
        if (is_null($media->getOriginal('model_id'))) {
            return;
        }

        if ($media->manipulations !== json_decode($media->getOriginal('manipulations'), true)) {
            $eventDispatcher = Media::getEventDispatcher();
            Media::unsetEventDispatcher();

            app(FileManipulator::class)->createDerivedFiles($media);

            Media::setEventDispatcher($eventDispatcher);
        }
    }

    public function deleted(Media $media)
    {
        if (in_array(SoftDeletes::class, class_uses_recursive($media))) {
            if (! $media->isForceDeleting()) {
                return;
            }
        }

        app(Filesystem::class)->removeAllFiles($media);
    }
}
