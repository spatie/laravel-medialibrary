<?php

namespace Spatie\MediaLibrary\HasMedia;

use Spatie\MediaLibrary\MediaCollection\MediaCollection;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Conversion\Conversion;

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
    public function hasMedia(string $collectionMedia = '') : bool;

    /**
     * Get media collection by its collectionName.
     *
     * @param string         $collectionName
     * @param array|callable $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMedia(string $collectionName = 'default', $filters = []);

    /**
     * Remove all media in the given collection.
     *
     * @param string $collectionName
     */
    public function clearMediaCollection(string $collectionName = 'default');

    /**
     * Remove all media in the given collection except some.
     *
     * @param string $collectionName
     * @param \Spatie\MediaLibrary\Media[]|\Illuminate\Support\Collection $excludedMedia
     *
     * @return string $collectionName
     */
    public function clearMediaCollectionExcept(string $collectionName = 'default', $excludedMedia = []);

    /**
     * Determines if the media files should be preserved when the media object gets deleted.
     *
     * @return bool
     */
    public function shouldDeletePreservingMedia();

    /**
     * Cache the media on the object.
     *
     * @param string $collectionName
     *
     * @return mixed
     */
    public function loadMedia(string $collectionName);

    /*
     * Add a conversion.
     */
    public function addMediaConversion(string $name): Conversion;

    /*
     * Register the media conversions.
     */
    public function registerMediaConversions(Media $media = null);

    /*
     * Register the media collections.
     */
    public function registerMediaCollections();

    /*
     * Register the media conversions and conversions set in media collections.
     */
    public function registerAllMediaConversions();

    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesValidationConstraints(string $collectionName): string;

    /**
     * Get a collection dimension validation constraints string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function dimensionValidationConstraints(string $collectionName): string;

    /**
     * Get registered collection max width and max height from its name.
     *
     * @param string $collectionName
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function collectionMaxSizes(string $collectionName): array;

    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\CollectionNotFound
     * @throws \Spatie\MediaLibrary\Exceptions\ConversionsNotFound
     */
    public function validationConstraints(string $collectionName): string;

    /**
     * Get the constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function constraintsLegend(string $collectionName): string;

    /**
     * Get a collection dimensions constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function dimensionsLegend(string $collectionName): string;

    /**
     * Get a collection mime types constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesLegend(string $collectionName): string;

    /**
     * Check if the given media collection should have dimension constraints, according to its declared accepted mime
     * types.
     *
     * @param \Spatie\MediaLibrary\MediaCollection\MediaCollection $collection
     *
     * @return bool
     */
    public function shouldHaveDimensionConstraints(MediaCollection $collection): bool;

    /**
     * Get declared conversions from a media collection name.
     *
     * @param string $collectionName
     *
     * @return array
     */
    public function getConversions(string $collectionName): array;

    /**
     * Get a media collection object from its name.
     *
     * @param string $collectionName
     *
     * @return \Spatie\MediaLibrary\MediaCollection\MediaCollection|null
     */
    public function getCollection(string $collectionName): ?MediaCollection;
}
