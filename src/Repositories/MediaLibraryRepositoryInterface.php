<?php namespace Spatie\MediaLibrary\Repositories;

use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelInterface;

interface MediaLibraryRepositoryInterface {

    public function getCollection(MediaLibraryModelInterface $model, $collectionName, $filters);

    public function add($file, MediaLibraryModelInterface $model, $collectionName, $preserveOriginal, $addAsTemporary);

    public function remove($id);

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
