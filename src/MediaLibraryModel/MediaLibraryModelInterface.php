<?php namespace Spatie\MediaLibrary\MediaLibraryModel;

interface MediaLibraryModelInterface
{
    /**
     * Get media collection by its collectionName.
     *
     * @param $collectionName
     * @param array $filters
     *
     * @return mixed
     */
    public function getMedia($collectionName, $filters = []);

    /**
     * Add media to media collection from a given file.
     *
     * @param $file
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     *
     * @return mixed
     */
    public function addMedia($file, $collectionName, $preserveOriginal = false, $addAsTemporary = false);

    /**
     * Remove a media item by its id.
     *
     * @param $id
     */
    public function removeMedia($id);

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media();

    /**
     * Get an array with the properties of the derived images.
     *
     * @return array
     */
    public function getImageProfileProperties();

    /**
     * Remove all media in the given collection.
     *
     * @param $collectionName
     * @return void
     */
    public function removeMediaCollection($collectionName);
}
