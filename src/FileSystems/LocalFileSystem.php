<?php namespace Spatie\MediaLibrary\FileSystems;

use File;
use Spatie\MediaLibrary\ImageManipulators\ImageManipulatorInterface;
use Spatie\MediaLibrary\Models\Media;

class LocalFileSystem implements FileSystemInterface
{
    protected $imageManipulator;

    public function __construct(ImageManipulatorInterface $imageManipulator)
    {
        $this->imageManipulator = $imageManipulator;
    }

    /**
     * Generate the needed files and directories to generated the derived files.
     *
     * @param $file
     * @param Media $media
     * @param $preserveOriginal
     */
    public function addFileForMedia($file, Media $media, $preserveOriginal)
    {
        $baseDirectory = $this->getBaseDirectoryForMedia($media);

        File::makeDirectory($baseDirectory, 493, true);

        $operation = ($preserveOriginal ? 'copy' : 'move');

        File::$operation($file, $baseDirectory.'/'.$media->path);

        $this->createDerivedFilesForMedia($media);
    }

    /**
     * Created the derived files for a Media-record.
     *
     * @param Media $media
     */
    public function createDerivedFilesForMedia(Media $media)
    {
        $this->imageManipulator->createDerivedFilesForMedia($media);
    }

    /**
     * Recursively delete the directory for a media.
     *
     * @param Media $media
     */
    public function removeFilesForMedia(Media $media)
    {
        if ($media && is_numeric($media->id)) {
            File::deleteDirectory($this->getBaseDirectoryForMedia($media));
        }
    }

    /**
     * Delete the derived files on the filesystem (except the original file).
     *
     * @param Media $media
     */
    public function removeDerivedFilesForMedia(Media $media)
    {
        foreach ($this->getFilePathsForMedia($media) as $profile => $path) {
            if ($profile != 'original') {
                File::delete($path);
            }
        }
    }

    /**
     * Get all file paths for a media's derived files.
     *
     * @param Media $media
     *
     * @return array
     */
    public function getFilePathsForMedia(Media $media)
    {
        $filePaths = [];

        foreach (File::allFiles($this->getBaseDirectoryForMedia($media)) as $file) {
            $profileName = explode('_', $file->getFileName())[0];

            $filePaths[($file->getFileName() == $media->path ? 'original' : $profileName)] = $file->getRealPath();
        }

        return $filePaths;
    }

    /**
     * Get a media's basedirectory (named by its id).
     *
     * @param Media $media
     *
     * @return string
     */
    public function getBaseDirectoryForMedia(Media $media)
    {
        return config('laravel-medialibrary.publicPath').'/'.$media->id;
    }
}
