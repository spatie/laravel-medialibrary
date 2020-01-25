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

        $original = $media->getOriginal('manipulations');

        if (! $this->isLaravel7OrHigher()) {
            $original = json_decode($original, true);
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

    private function isLaravel7OrHigher(): bool
    {
        if (Application::VERSION === '7.x-dev') {
            return true;
        }

        if (version_compare(Application::VERSION, '7.0', '>=')) {
            return true;
        }

        return false;
    }
}
