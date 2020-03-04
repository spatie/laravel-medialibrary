<?php

namespace Spatie\Medialibrary;

use Illuminate\Support\Collection;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\MediaCollections\FileAdder;
use Spatie\Medialibrary\MediaCollections\Models\Media;

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
     * @return \Spatie\Medialibrary\MediaCollections\FileAdder
     */
    public function addMedia($file): FileAdder;

    /**
     * Copy a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\Medialibrary\MediaCollections\FileAdder
     */
    public function copyMedia($file): FileAdder;

    public function hasMedia(string $collectionMedia = ''): bool;

    /**
     * Get media collection by its collectionName.
     *
     * @param string         $collectionName
     * @param array|callable $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMedia(string $collectionName = 'default', $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): self;

    /**
     * Remove all media in the given collection except some.
     *
     * @param string $collectionName
     * @param \Spatie\Medialibrary\Media[]|\Illuminate\Support\Collection $excludedMedia
     *
     * @return $this
     */
    public function clearMediaCollectionExcept(string $collectionName = 'default', $excludedMedia = []): self;

    /**
     * Determines if the media files should be preserved when the media object gets deleted.
     *
     * @return bool
     */
    public function shouldDeletePreservingMedia(): bool;

    /**
     * Cache the media on the object.
     *
     * @param string $collectionName
     *
     * @return mixed
     */
    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;
}
