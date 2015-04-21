<?php namespace Spatie\MediaLibrary\MediaLibraryModel;

interface MediaLibraryModelInterface {

    /**
     * Get media collection by its collectionName
     *
     * @param $collectionName
     * @param array $filters
     * @return mixed
     */
    public function getMedia($collectionName, $filters = []);

    /**
     * Add media to media collection from a given file
     *
     * @param $file
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     * @return mixed
     */
    public static function addMedia($file, $collectionName, $preserveOriginal = false, $addAsTemporary = false);

    /**
     * Remove a media item by its id
     *
     * @param $id
     */
    public static function removeMedia($id);

    /**
     * Set the polymorphic relation
     *
     * @return mixed
     */
    public static function media();
}
