<?php

namespace Spatie\MediaLibrary\HasMedia;

use Illuminate\Contracts\Events\Dispatcher;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Exceptions\MediaDoesNotBelongToModel;
use Spatie\MediaLibrary\Exceptions\MediaIsNotPartOfCollection;
use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeOpened;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory;
use Spatie\MediaLibrary\Filesystem;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\MediaRepository;

trait HasMediaTrait
{
    /**
     * @var array
     */
    public $mediaConversions = [];

    /**
     * @var bool
     */
    protected $deletePreservingMedia = false;

    public static function bootHasMediaTrait()
    {
        static::deleted(function ($entity) {
            if (!$entity->shouldDeletePreservingMedia()) {
                $entity->media()->get()->map(function (Media $media) {
                    $media->delete();
                });
            }
        });
    }

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media()
    {
        return $this->morphMany(config('laravel-medialibrary.media_model'), 'model');
    }

    /**
     * Add a file to the medialibrary. The file will be removed from
     * it's original location.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMedia($file)
    {
        return app(FileAdderFactory::class)->create($this, $file);
    }

    /**
     * Add a remote file to the medialibrary.
     *
     * @param $url
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCouldNotBeOpened
     */
    public function addMediaFromUrl($url)
    {
        if (!$stream = @fopen($url, 'r')) {
            throw new UrlCouldNotBeOpened();
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');
        file_put_contents($tmpFile, $stream);

        $filename = basename(parse_url($url, PHP_URL_PATH));

        return app(FileAdderFactory::class)
            ->create($this, $tmpFile)
            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
            ->usingFileName($filename);
    }

    /**
     * Copy a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function copyMedia($file)
    {
        return $this->addMedia($file)->preservingOriginal();
    }

    /**
     * Determine if there is media in the given collection.
     *
     * @param $collectionName
     *
     * @return bool
     */
    public function hasMedia($collectionName = '')
    {
        return count($this->getMedia($collectionName)) ? true : false;
    }

    /**
     * Get media collection by its collectionName.
     *
     * @param string $collectionName
     * @param array  $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMedia($collectionName = '', $filters = [])
    {
        return app(MediaRepository::class)->getCollection($this, $collectionName, $filters);
    }

    /**
     * Get the first media item of a media collection.
     *
     * @param string $collectionName
     * @param array  $filters
     *
     * @return bool|Media
     */
    public function getFirstMedia($collectionName = 'default', $filters = [])
    {
        $media = $this->getMedia($collectionName, $filters);

        return count($media) ? $media->first() : false;
    }

    /**
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     *
     * @param string $collectionName
     * @param string $conversionName
     *
     * @return string
     */
    public function getFirstMediaUrl($collectionName = 'default', $conversionName = '')
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return false;
        }

        return $media->getUrl($conversionName);
    }

    /**
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     *
     * @param string $collectionName
     * @param string $conversionName
     *
     * @return string
     */
    public function getFirstMediaPath($collectionName = 'default', $conversionName = '')
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return false;
        }

        return $media->getPath($conversionName);
    }

    /**
     * Update a media collection by deleting and inserting again with new values.
     *
     * @param array  $newMediaArray
     * @param string $collectionName
     *
     * @return array
     *
     * @throws \Spatie\MediaLibrary\Exceptions\MediaIsNotPartOfCollection
     */
    public function updateMedia(array $newMediaArray, $collectionName = 'default')
    {
        $this->removeMediaItemsNotPresentInArray($newMediaArray, $collectionName);

        $orderColumn = 1;

        $updatedMedia = [];
        foreach ($newMediaArray as $newMediaItem) {
            $mediaClass = config('laravel-medialibrary.media_model');
            $currentMedia = $mediaClass::findOrFail($newMediaItem['id']);

            if ($currentMedia->collection_name != $collectionName) {
                throw new MediaIsNotPartOfCollection(
                    sprintf('Media id %s is not part of collection %s', $currentMedia->id, $collectionName)
                );
            }

            if (array_key_exists('name', $newMediaItem)) {
                $currentMedia->name = $newMediaItem['name'];
            }

            if (array_key_exists('custom_properties', $newMediaItem)) {
                $currentMedia->custom_properties = $newMediaItem['custom_properties'];
            }

            $currentMedia->order_column = $orderColumn++;

            $currentMedia->save();

            $updatedMedia[] = $currentMedia;
        }

        return $updatedMedia;
    }

    /**
     * @param array  $newMediaArray
     * @param string $collectionName
     */
    protected function removeMediaItemsNotPresentInArray(array $newMediaArray, $collectionName = 'default')
    {
        $this->getMedia($collectionName, [])
            ->filter(function ($currentMediaItem) use ($newMediaArray) {
                return !in_array($currentMediaItem->id, collect($newMediaArray)->lists('id')->toArray());
            })
            ->map(function ($media) {
                $media->delete();
            });
    }

    /**
     * Remove all media in the given collection.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function clearMediaCollection($collectionName = 'default')
    {
        $this->getMedia($collectionName)->map(function ($media) {
            app(Filesystem::class)->removeFiles($media);
            $media->delete();
        });

        app(Dispatcher::class)->fire(new CollectionHasBeenCleared($this, $collectionName));

        return $this;
    }

    /**
     * Delete the associated media with the given id.
     * You may also pass a media object.
     *
     * @param int|\Spatie\MediaLibrary\Media $mediaId
     *
     * @throws \Spatie\MediaLibrary\Exceptions\MediaDoesNotBelongToModel
     */
    public function deleteMedia($mediaId)
    {
        if ($mediaId instanceof Media) {
            $mediaId = $mediaId->id;
        }

        $media = $this->media->find($mediaId);

        if (!$media) {
            throw new MediaDoesNotBelongToModel('Media id '.$mediaId.' does not belong to this model');
        }

        $media->delete();
    }

    /**
     * Add a conversion.
     *
     * @param string $name
     *
     * @return \Spatie\MediaLibrary\Conversion\Conversion
     */
    public function addMediaConversion($name)
    {
        $conversion = Conversion::create($name);

        $this->mediaConversions[] = $conversion;

        return $conversion;
    }

    /**
     * Delete the model, but preserve all the associated media.
     *
     * @return bool
     */
    public function deletePreservingMedia()
    {
        $this->deletePreservingMedia = true;

        return $this->delete();
    }

    /**
     * Determines if the media files should be preserved when the media object gets deleted.
     *
     * @return \Spatie\MediaLibrary\Media
     */
    public function shouldDeletePreservingMedia()
    {
        if (isset($this->deletePreservingMedia)) {
            return $this->deletePreservingMedia;
        }

        return false;
    }
}
