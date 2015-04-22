<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\FileSystems\FileSystemInterface;
use Spatie\MediaLibrary\MediaLibraryModel\MediaLibraryModelInterface;
use Spatie\MediaLibrary\Models\Media;
use Carbon\Carbon;

class MediaLibraryRepository
{
    protected $fileSystem;

    /**
     * @param FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Add a file to the media collection of the given model
     *
     * @param $file
     * @param MediaLibraryModelInterface $model
     * @param $collectionName
     * @param bool $preserveOriginal if this is set to true the file will be removed from it's original location
     * @param bool $addAsTemporary
     *
     * @return Media
     */
    public function add($file, MediaLibraryModelInterface $model, $collectionName, $preserveOriginal = false, $addAsTemporary = false)
    {
        $media = $this->createMediaForFile($file, $collectionName, $addAsTemporary);

        $model->media()->save($media);

        $this->fileSystem->addFileForMedia($file, $media, $preserveOriginal);

        $this->addURLsToMediaItem($media);

        return $media;
    }

    /**
     * Remove a media record and it's associated files.
     *
     * @param $id
     *
     * @return bool
     *
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
     * Reorder media-records.
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
     * Get a collection of media by its collectionName.
     *
     * @param MediaLibraryModelInterface $model
     * @param $collectionName
     * @param $filters
     *
     * @return mixed
     */
    public function getCollection(MediaLibraryModelInterface $model, $collectionName, $filters)
    {
        $mediaItems = $this->loadMedia($model, $collectionName);

        $media = $this->addURLsToMediaProfile($mediaItems);

        $media = $this->applyFiltersToMedia($media, $filters);

        return $media;
    }

    /**
     * Clean up temporary media records.
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
     * Regenerate the derived media files.
     *
     * @param $media
     *
     * @return $this
     */
    public function regenerateDerivedFiles($media)
    {
        $this->fileSystem->removeDerivedFilesForMedia($media);
        $this->fileSystem->createDerivedFilesForMedia($media);

        return $this;
    }

    /**
     * Create a new media-record for a file.
     *
     * @param $file
     * @param $collectionName
     * @param $addAsTemporary
     *
     * @return Media
     */
    private function createMediaForFile($file, $collectionName, $addAsTemporary)
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
     * Apply given filters on media.
     *
     * @param $media
     * @param $filters
     *
     * @return mixed
     */
    private function applyFiltersToMedia(Collection $media, $filters)
    {
        foreach ($filters as $filterProperty => $filterValue) {
            $media = $media->filter(function ($media) use ($filterProperty, $filterValue) {
                return $media->$filterProperty == $filterValue;
            });
        }

        return $media;
    }

    /**
     * Add URL to profile-image media.
     *
     * @param array $media
     *
     * @return mixed
     */
    private function addURLsToMediaProfile(array $media)
    {
        foreach ($media as $mediaKey => $mediaItem) {
            $media[$mediaKey] = $this->addURLsToMediaItem($mediaItem);
        }

        return $media;
    }

    /**
     * Add URL to a single media item.
     *
     * @param $mediaItem
     *
     * @return mixed
     */
    private function addURLsToMediaItem($mediaItem)
    {
        foreach ($this->fileSystem->getFilePathsForMedia($mediaItem) as $profileName => $filePath) {
            $mediaItem->addImageProfileURL($profileName, str_replace(public_path(), '', $filePath));
        }

        return $mediaItem;
    }

    /**
     * Load media by collectionName.
     *
     * @param MediaLibraryModelInterface $model
     * @param $collectionName
     *
     * @return mixed
     */
    private function loadMedia(MediaLibraryModelInterface $model, $collectionName)
    {
        if ($this->mediaIsPreloaded($model)) {
            $media = $model->media->filter(function (Media $mediaItem) use ($collectionName) {
                return $mediaItem->collection_name == $collectionName;
            })->sortBy(function (Media $media) {
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
     * Check if the media is preloaded.
     *
     * @param MediaLibraryModelInterface $model
     *
     * @return bool
     */
    private function mediaIsPreloaded(MediaLibraryModelInterface $model)
    {
        if (isset($model->media)) {
            return true;
        }

        return false;
    }




}
