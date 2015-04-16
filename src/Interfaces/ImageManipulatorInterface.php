<?php namespace Spatie\MediaLibrary\Interfaces;

use Spatie\MediaLibrary\Models\Media;

interface ImageManipulatorInterface {

    public function createDerivedFilesForMedia(Media $media);

}
