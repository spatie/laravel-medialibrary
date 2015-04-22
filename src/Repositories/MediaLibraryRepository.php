<?php namespace Spatie\MediaLibrary\Repositories;

use Spatie\MediaLibrary\FileSystems\FileSystemInterface;
use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelInterface;
use Spatie\MediaLibrary\Models\Media;
use Carbon\Carbon;

class MediaLibraryRepository implements MediaLibraryRepositoryInterface
{
    protected $fileSystem;

    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Add a new media to a Models mediaCollection
     *
     * @param $file
     * @param MediaLibraryModelInterface $model
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     * @return Media
     */
    public function add($file, MediaLibraryModelInterface $model, $collectionName, $preserveOriginal = false, $addAsTemporary = false)
    {
        $media = $this->createMediaFromFile($file, $collectionName, $addAsTemporary);

        $model->media()->save($media);

        $this->fileSystem->addFileForMedia($file, $media, $preserveOriginal);

        return $media;
    }

    /**
     * Remove a media record and it's associated files
     *
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function remove($id)
    {
        $media = Media::findOrFail($id);

        $this->fileSystem->removeFilesForMedia($media);

        $media->delete();

        return true;
    }

    /**
     * Reorder media-records
     *
     * @param $orderArray
     * @param MediaLibraryModelInterface $model
     */
    public function order($orderArray, MediaLibraryModelInterface $model)
    {
        $orderCounter = 0;
        foreach ($orderArray as $id => $order) {
            $media = Media::findOrFail($id);

            if ($media->content_type == get_class($model) && $media->content_id == $model->id) {
                $media->order_column = $orderCounter++;
                $media->save();
            }
        }
    }

    /**
     * Get a collection of media by its collectionName
     *
     * @param MediaLibraryModelInterface $model
     * @param $collectionName
     * @param $filters
     * @return mixed
     */
    public function getCollection(MediaLibraryModelInterface $model, $collectionName, $filters)
    {
        $media = $this->loadMedia($model, $collectionName);

        $media = $this->addURLToMediaProfile($media);

        $media = $this->applyFiltersToMedia($media, $filters);

        return $media;
    }

    /**
     * Clean up temporary media records
     *
     * @return int
     */
    public function cleanUp()
    {
        $media = Media::where('temp', 1)
            ->where('created_at', '<=', Carbon::now()->subDay(1)->toDateTimeString())
            ->get();

        foreach ($media as $mediaItem) {
            $this->remove($mediaItem->id);
        }

        return count($media);
    }

    /**
     * Regenerate the derived media files
     *
     * @param $media
     * @return $this
     */
    public function regenerateDerivedFiles($media)
    {
        $this->fileSystem->removeDerivedFilesForMedia($media);
        $this->fileSystem->createDerivedFilesForMedia($media);

        return $this;
    }

    /**
     * Create a new media-records from a filepath and collectionName
     *
     * @param $file
     * @param $collectionName
     * @param $addAsTemporary
     * @return Media
     */
    private function createMediaFromFile($file, $collectionName, $addAsTemporary)
    {
        $pathParts = pathinfo($file);

        $media = new Media();

        $media->name = $pathParts['filename'];
        $media->url = $pathParts['basename'];
        $media->path = $pathParts['basename'];
        $media->extension = strtolower($pathParts['extension']);
        $media->size = filesize($file);
        $media->temp = $addAsTemporary;
        $media->collection_name = $collectionName;
        $media->order_column = Media::getHighestNumberOrder();

        return $media;
    }

    /**
     * Load media by collectionName
     *
     * @param MediaModelInterface $model
     * @param $collectionName
     * @return mixed
     */
    private function loadMedia(MediaModelInterface $model, $collectionName)
    {
        if ($this->mediaIsPreloaded($model)) {

            $media = $model->media->filter(function ($mediaItem) use ($collectionName) {
                return $mediaItem->collection_name == $collectionName;
            })->sortBy(function ($media) {
                return $media->order_column;
            })->values();

            return $media;
        }

        $media = $model->media()
            ->where('collection_name', $collectionName)
            ->orderBy('order_column')->get();

        return $media;
    }

    /**
     * Check if the media is preloaded
     *
     * @param MediaModelInterface $model
     * @return bool
     */
    private function mediaIsPreloaded(MediaModelInterface $model)
    {
        if (isset($model->media)) {
            return true;
        }

        return false;
    }

    /**
     * Add URL to profile-image media
     *
     * @param $media
     * @return mixed
     */
    private function addURLToMediaProfile($media)
    {
        foreach($media as $mediaKey => $mediaItem)
        {
            $media[$mediaKey] = $this->addURLToMediaItem($mediaItem);
        }

        return $media;
    }

    /**
     * Add URL to a single media item
     *
     * @param $mediaItem
     * @return mixed
     */
    private function addURLToMediaItem($mediaItem)
    {
        foreach ($this->fileSystem->getFilePathsForMedia($mediaItem) as $profileName => $filePath) {
            $mediaItem->addImageProfileURL($profileName, str_replace(public_path(), '', $filePath));
        }

        return $mediaItem;
    }

    /**
     * Apply given filters on media
     *
     * @param $media
     * @param $filters
     * @return mixed
     */
    private function applyFiltersToMedia($media, $filters)
    {
        foreach ($filters as $filterProperty => $filterValue) {

            $media = $media->filter(function ($media) use ($filterProperty, $filterValue) {
                return $media->$filterProperty == $filterValue;
            });
        }

        return $media;
    }
}
