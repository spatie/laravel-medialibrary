<?php namespace Spatie\MediaLibrary\FileSystems;

use Spatie\MediaLibrary\Media;

interface FileSystemInterface
{
    /**
     * Generate the needed files and directories to generated the derived files.
     *
     * @param $file
     * @param Media $media
     * @param $preserveOriginal
     */
    public function addFileForMedia($file, Media $media, $preserveOriginal);

    /**
     * Recursively delete the directory for a media.
     *
     * @param Media $media
     */
    public function removeFilesForMedia(Media $media);

    /**
     * Delete the derived files on the filesystem (except the original file).
     *
     * @param Media $media
     */
    public function removeDerivedFilesForMedia(Media $media);

    /**
     * Get all file paths for a media's derived files.
     *
     * @param Media $media
     *
     * @return array
     */
    public function getFilePathsForMedia(Media $media);

    /**
     * Created the derived files for a Media-record.
     *
     * @param Media $media
     */
    public function createDerivedFilesForMedia(media $media);
}
