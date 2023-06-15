<?php

namespace Programic\MediaLibrary\MediaCollections;

use Programic\MediaLibrary\HasMedia;
use Programic\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeUpdated;
use Programic\MediaLibrary\MediaCollections\Models\Media;

/**
 * @template TModel of \Programic\MediaLibrary\MediaCollections\Models\Media
 */
class FileReplacer extends FileAdder
{
    public function __construct(
        protected Media $originalMedia,
        protected ?Filesystem $filesystem
    ) {
        if (! $this->originalMedia->attachable) throw new MediaCannotBeUpdated('Media is not attachable.');

        parent::__construct($filesystem);
    }

    protected function processMediaItem(?HasMedia $model, Media $media, FileAdder $fileAdder)
    {
        parent::processMediaItem($model, $media, $fileAdder);

        $this->originalMedia->mediable()->update([
            $this->originalMedia->mediable()->getForeignKeyName() => $media->getKey(),
        ]);

        $this->originalMedia->forceDelete();
    }
}
