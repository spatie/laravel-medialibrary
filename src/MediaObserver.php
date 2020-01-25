<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Application;
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

        if (Application::VERSION === '7.x-dev' || version_compare(Application::VERSION, '7.0', '>=')) {
            $original = $media->getOriginal('manipulations');
        } else {
            $original = json_decode($media->getOriginal('manipulations'), true);
        }

        if ($media->manipulations !== $original) {
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
