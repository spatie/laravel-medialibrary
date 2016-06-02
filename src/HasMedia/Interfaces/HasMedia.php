<?php

namespace Spatie\MediaLibrary\HasMedia\Interfaces;

interface HasMedia
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media();

    /**
     * Move a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMedia($file);

    /**
     * Copy a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function copyMedia($file);

    /**
     * Determine if there is media in the given collection.
     *
     * @param $collectionMedia
     *
     * @return bool
     */
    public function hasMedia($collectionMedia = '');

    /**
     * Get media collection by its collectionName.
     *
     * @param string $collectionName
     * @param array  $filters
     *
     * @return \Spatie\MediaLibrary\Media
     */
    public function getMedia($collectionName = 'default', $filters = []);

    /**
     * Remove all media in the given collection.
     *
     * @param string $collectionName
     */
    public function clearMediaCollection($collectionName = 'default');

    /**
     * Determines if the media files should be preserved when the media object gets deleted.
     *
     * @return \Spatie\MediaLibrary\Media
     */
    public function shouldDeletePreservingMedia();
}
