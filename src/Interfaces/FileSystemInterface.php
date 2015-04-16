<?php namespace Spatie\MediaLibrary\Interfaces;

use Spatie\MediaLibrary\Models\Media;

interface FileSystemInterface {

    public function addFileForMedia($file, Media $media, $preserveOriginal);

    public function removeFilesForMedia(Media $media);

    public function removeDerivedFilesForMedia(Media $media);

    public function getFilePathsForMedia(Media $media);

    public function createDerivedFilesForMedia(media $media);
}
