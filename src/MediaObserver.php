<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Filesystem\Filesystem;

class MediaObserver
{
    public function creating(Media $media)
    {
        $media->setHighestOrderNumber();
    }

    public function updating(Media $media)
    {
        if ($media->file_name !== $media->getOriginal('file_name')) {
            app(Filesystem::class)->renameFile($media, $media->getOriginal('file_name'));
        }
    }

    public function updated(Media $media)
    {
        if (is_null($media->getOriginal('model_id'))) {
            return;
        }

        if ($media->manipulations !== json_decode($media->getOriginal('manipulations'))) {
            app(FileManipulator::class)->createDerivedFiles($media);
        }
    }

    public function deleted(Media $media)
    {
        $softDeleted = false;
        
        if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses($media))){
            $softDeleted = $this->isSoftDeleted($media);
        }
        
        if(!$softDeleted){
            app(Filesystem::class)->removeFiles($media);
        }
    }

    private function isSoftDeleted(Media $media)
    {
        return $media->isDirty($media->getDeletedAtColumn());
    }
}
