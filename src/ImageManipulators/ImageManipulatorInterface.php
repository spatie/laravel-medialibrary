<?php namespace Spatie\MediaLibrary\ImageManipulators;

use Spatie\MediaLibrary\Models\Media;

interface ImageManipulatorInterface {

    public function createDerivedFilesForMedia(Media $media);

}
