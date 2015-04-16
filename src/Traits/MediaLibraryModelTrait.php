<?php namespace Spatie\MediaLibrary\Traits;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Models\Media;
use Exception;
use Spatie\MediaLibrary\MediaLibraryFacade as MediaLibrary;

trait MediaLibraryModelTrait {

    public static function boot()
    {
        parent::boot();

        self::deleting(function($subject) {

            foreach($subject->media()->get() as $media)
            {
                MediaLibrary::remove($media->id);
            }
        });
    }

    /**
     * Get media collection by its collectionName
     *
     * @param $collectionName
     * @param array $filters
     * @return mixed
     */
    public function getMedia($collectionName, $filters = ['temp' => 1])
    {
        return MediaLibrary::getCollection($this, $collectionName, $filters);
    }

    /**
     * Get the first media item of a media collection
     *
     * @param $collectionName
     * @param array $filters
     * @return bool
     */
    public function getFirstMedia($collectionName, $filters = [])
    {
        $media = $this->getMedia($collectionName, $filters);

        return (count($media) ? $media[0] : false);
    }

    /**
     * Add media to media collection from a given file
     *
     * @param $file
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     * @return mixed
     */
    public function addMedia($file, $collectionName, $preserveOriginal = false, $addAsTemporary = true)
    {
        $media = MediaLibrary::add($file, $this, $collectionName, $preserveOriginal, $addAsTemporary);

        return $media;
    }

    /**
     * Remove a media item by its id
     *
     * @param $id
     */
    public function removeMedia($id)
    {
        $media = Media::findOrFail($id);

        if($media->content_type == get_class($this) && $media->content_id == $this->id)
        {
            MediaLibrary::remove($id);
        }
    }

    /**
     * Update a media collection by deleting and inserting again with new values
     *
     * @param array $newMediaArray
     * @param $collectionName
     * @throws Exception
     */
    public function updateMedia(array $newMediaArray, $collectionName)
    {
        $mediaItems = new Collection($newMediaArray);

        foreach($this->getMedia($collectionName, []) as $currentMedia)
        {
            if( ! in_array($currentMedia->id, $mediaItems->lists('id')))
            {
                $this->removeMedia($currentMedia->id);
            }
        }

        $orderCounter = 0;

        foreach($newMediaArray as $newMediaItem)
        {
            $currentMedia = Media::findOrFail($newMediaItem['id']);

            if($currentMedia->collection_name != $collectionName)
            {
                throw new Exception('Media id: ' . $currentMedia->id . ' error: Updating the wrong collection. Expected: "' . $collectionName . '" - got: "'. $currentMedia->collection_name);
            }

            if(array_key_exists('name', $newMediaItem))
            {
                $currentMedia->name = $newMediaItem['name'];
            }

            $currentMedia->order_column = $orderCounter++;

            $currentMedia->temp = 0;

            $currentMedia->save();
        }
    }

    /**
     * Set the polymorphic relation
     *
     * @return mixed
     */
    public function media()
    {
        return $this->morphMany('Spatie\MediaLibrary\Models\Media', 'content');
    }
}
