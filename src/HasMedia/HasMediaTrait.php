<?php

namespace Spatie\MediaLibrary\HasMedia;

use Spatie\MediaLibrary\Media;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Spatie\MediaLibrary\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted;
use Spatie\MediaLibrary\Exceptions\MediaCannotBeUpdated;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\InvalidBase64Data;

trait HasMediaTrait
{
    /** @var array */
    public $mediaConversions = [];

    /** @var bool */
    protected $deletePreservingMedia = false;

    public static function bootHasMediaTrait()
    {
        static::deleted(function (HasMedia $entity) {
            if ($entity->shouldDeletePreservingMedia()) {
                return;
            }

            $entity->media()->get()->each->delete();
        });
    }

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media()
    {
        return $this->morphMany(config('medialibrary.media_model'), 'model');
    }

    /**
     * Add a file to the medialibrary.
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
     * Add a file from a request.
     *
     * @param string $key
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMediaFromRequest(string $key)
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $key);
    }

    /**
     * Add multiple files from a request by keys.
     *
     * @param string[] $keys
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder[]
     */
    public function addMultipleMediaFromRequest(array $keys)
    {
        return app(FileAdderFactory::class)->createMultipleFromRequest($this, $keys);
    }

    /**
     * Add all files from a request.
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder[]
     */
    public function addAllMediaFromRequest()
    {
        return app(FileAdderFactory::class)->createAllFromRequest($this);
    }

    /**
     * Add a remote file to the medialibrary.
     *
     * @param string $url
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function addMediaFromUrl(string $url)
    {
        if (! $stream = @fopen($url, 'r')) {
            throw UnreachableUrl::create($url);
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
     * Add a base64 encoded file to the medialibrary.
     *
     * @param string $base64data
     *
     * @throws InvalidBase64Data
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMediaFromBase64(string $base64data)
    {
        // strip out data uri scheme information (see RFC 2397)
        if (strpos($base64data, ';base64') !== false) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            throw InvalidBase64Data::create();
        }

        // decoding and then reeconding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            throw InvalidBase64Data::create();
        }

        $binaryData = base64_decode($base64data);

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);

        $file = app(FileAdderFactory::class)
            ->create($this, $tmpFile);

        return $file;
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

    /*
     * Determine if there is media in the given collection.
     */
    public function hasMedia(string $collectionName = 'default'): bool
    {
        return count($this->getMedia($collectionName)) ? true : false;
    }

    /**
     * Get media collection by its collectionName.
     *
     * @param string $collectionName
     * @param array|callable  $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMedia(string $collectionName = 'default', $filters = []): Collection
    {
        return app(MediaRepository::class)->getCollection($this, $collectionName, $filters);
    }

    /**
     * Get the first media item of a media collection.
     *
     * @param string $collectionName
     * @param array $filters
     *
     * @return Media|null
     */
    public function getFirstMedia(string $collectionName = 'default', array $filters = [])
    {
        $media = $this->getMedia($collectionName, $filters);

        return $media->first();
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstMedia($collectionName);

        if (! $media) {
            return '';
        }

        return $media->getUrl($conversionName);
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstMedia($collectionName);

        if (! $media) {
            return '';
        }

        return $media->getPath($conversionName);
    }

    /**
     * Update a media collection by deleting and inserting again with new values.
     *
     * @param array $newMediaArray
     * @param string $collectionName
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \Spatie\MediaLibrary\Exceptions\MediaCannotBeUpdated
     */
    public function updateMedia(array $newMediaArray, string $collectionName = 'default'): Collection
    {
        $this->removeMediaItemsNotPresentInArray($newMediaArray, $collectionName);

        return collect($newMediaArray)
            ->map(function (array $newMediaItem) use ($collectionName) {
                static $orderColumn = 1;

                $mediaClass = config('medialibrary.media_model');
                $currentMedia = $mediaClass::findOrFail($newMediaItem['id']);

                if ($currentMedia->collection_name != $collectionName) {
                    throw MediaCannotBeUpdated::doesNotBelongToCollection($collectionName, $currentMedia);
                }

                if (array_key_exists('name', $newMediaItem)) {
                    $currentMedia->name = $newMediaItem['name'];
                }

                if (array_key_exists('custom_properties', $newMediaItem)) {
                    $currentMedia->custom_properties = $newMediaItem['custom_properties'];
                }

                $currentMedia->order_column = $orderColumn++;

                $currentMedia->save();

                return $currentMedia;
            });
    }

    /**
     * @param array $newMediaArray
     * @param string $collectionName
     */
    protected function removeMediaItemsNotPresentInArray(array $newMediaArray, string $collectionName = 'default')
    {
        $this->getMedia($collectionName)
            ->reject(function (Media $currentMediaItem) use ($newMediaArray) {
                return in_array($currentMediaItem->id, array_column($newMediaArray, 'id'));
            })
            ->each->delete();
    }

    /**
     * Remove all media in the given collection.
     *
     * @param string $collectionName
     *
     * @return $this
     */
    public function clearMediaCollection(string $collectionName = 'default')
    {
        $this->getMedia($collectionName)
            ->each(function (Media $media) {
                app(Filesystem::class)->removeFiles($media);
                $media->delete();
            });

        event(new CollectionHasBeenCleared($this, $collectionName));

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }

        return $this;
    }

    /**
     * Delete the associated media with the given id.
     * You may also pass a media object.
     *
     * @param int|\Spatie\MediaLibrary\Media $mediaId
     *
     * @throws \Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted
     */
    public function deleteMedia($mediaId)
    {
        if ($mediaId instanceof Media) {
            $mediaId = $mediaId->id;
        }

        $media = $this->media->find($mediaId);

        if (! $media) {
            throw MediaCannotBeDeleted::doesNotBelongToModel($media, $this);
        }

        $media->delete();
    }

    /*
     * Add a conversion.
     */
    public function addMediaConversion(string $name): Conversion
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
    public function deletePreservingMedia(): bool
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
        return $this->deletePreservingMedia ?? false;
    }

    protected function mediaIsPreloaded(): bool
    {
        return isset($this->media);
    }

    /**
     * Cache the media on the object.
     *
     * @param string $collectionName
     *
     * @return mixed
     */
    public function loadMedia(string $collectionName)
    {
        if ($this->mediaIsPreloaded()) {
            return $this->media
                ->filter(function (Media $mediaItem) use ($collectionName) {
                    if ($collectionName == '') {
                        return true;
                    }

                    return $mediaItem->collection_name === $collectionName;
                })
                ->sortBy('order_column')
                ->values();
        }

        $query = $this->media();

        if ($collectionName !== '') {
            $query = $query->where('collection_name', $collectionName);
        }

        return $query
            ->orderBy('order_column')
            ->get();
    }
}
