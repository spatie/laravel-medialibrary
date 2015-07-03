<?php namespace Spatie\MediaLibrary\Traits;

interface HasMediaInterface
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media();

    /**
     * Add media to media collection from a given file.
     *
     * @param $file
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     *
     * @return Media
     */
    public function addMedia($file, $collectionName, $preserveOriginal = false, $addAsTemporary = false);

    /**
     * Get media collection by its collectionName.
     *
     * @param $collectionName
     * @param array $filters
     *
     * @return \Spatie\MediaLibrary\Media
     */
    public function getMedia($collectionName, $filters = []);


    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions();

    /**
     * Add a conversion.
     *
     * @return \Spatie\MediaLibrary\Conversion\Conversion;
     */
    public function addMediaConversion($name);

    /**
     * Remove all media in the given collection.
     *
     * @param $collectionName
     */
    public function clearMediaCollection($collectionName);
}
