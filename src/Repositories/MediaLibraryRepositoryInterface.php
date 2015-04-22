<?php namespace Spatie\MediaLibrary\Repositories;

use Spatie\MediaLibrary\Models\MediaModelInterface;

interface MediaLibraryRepositoryInterface {

    public function getCollection(MediaModelInterface $model, $collectionName, $filters);

    /**
     * Add a new media to a Models mediaCollection
     *
     * @param $file
     * @param MediaModelInterface $model
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     * @return Media
     */
    public function add($file, MediaModelInterface $model, $collectionName, $preserveOriginal, $addAsTemporary);

    /**
     * Remove a media record and it's associated files
     *
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function remove($id);

    /**
     * Reorder media-records
     *
     * @param $orderArray
     * @param MediaModelInterface $model
     */
    public function order($orderArray, MediaModelInterface $model);

    /**
     *
     * Clean temp files older than 1 day
     *
     * @return int The amount of deleted files
     *
     */
    public function cleanUp();
}
