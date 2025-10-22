<?php

namespace Spatie\MediaLibrary;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Downloaders\DefaultDownloader;
use Spatie\MediaLibrary\Enums\CollectionPosition;
use Spatie\MediaLibrary\MediaCollections\Events\CollectionHasBeenClearedEvent;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidUrl;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeUpdated;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\MediaLibraryPro;
use Spatie\MediaLibraryPro\PendingMediaLibraryRequestHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @template TMedia of \Spatie\MediaLibrary\MediaCollections\Models\Media = \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
trait InteractsWithMedia
{
    /** @var Conversion[] */
    public array $mediaConversions = [];

    /** @var MediaCollection[] */
    public array $mediaCollections = [];

    protected bool $deletePreservingMedia = false;

    protected array $unAttachedMediaLibraryItems = [];

    public static function bootInteractsWithMedia(): void
    {
        static::deleting(function (HasMedia $model) {
            if ($model->shouldDeletePreservingMedia()) {
                return;
            }

            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->deleteAllMedia();
        });
    }

    /**
     * @return MorphMany<TMedia, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany($this->getMediaModel(), 'model');
    }

    /**
     * Add a file to the media library.
     *
     * @return FileAdder<TMedia>
     */
    public function addMedia(string|UploadedFile $file): FileAdder
    {
        return app(FileAdderFactory::class)->create($this, $file);
    }

    /**
     * @return FileAdder<TMedia>
     */
    public function addMediaFromRequest(string $key): FileAdder
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $key);
    }

    /**
     * Add a file from the given disk.
     *
     * @return FileAdder<TMedia>
     */
    public function addMediaFromDisk(string $key, ?string $disk = null): FileAdder
    {
        return app(FileAdderFactory::class)->createFromDisk($this, $key, $disk ?: config('filesystems.default'));
    }

    public function addFromMediaLibraryRequest(?array $mediaLibraryRequestItems): PendingMediaLibraryRequestHandler
    {
        MediaLibraryPro::ensureInstalled();

        return new PendingMediaLibraryRequestHandler(
            $mediaLibraryRequestItems ?? [],
            $this,
            $preserveExisting = true
        );
    }

    public function syncFromMediaLibraryRequest(?array $mediaLibraryRequestItems): PendingMediaLibraryRequestHandler
    {
        MediaLibraryPro::ensureInstalled();

        return new PendingMediaLibraryRequestHandler(
            $mediaLibraryRequestItems ?? [],
            $this,
            $preserveExisting = false
        );
    }

    /**
     * Add multiple files from a request by keys.
     *
     * @param  string[]  $keys
     * @return Collection<int, FileAdder<TMedia>>
     */
    public function addMultipleMediaFromRequest(array $keys): Collection
    {
        return app(FileAdderFactory::class)->createMultipleFromRequest($this, $keys);
    }

    /**
     * Add all files from a request.
     *
     * @return Collection<int, FileAdder<TMedia>>
     */
    public function addAllMediaFromRequest(): Collection
    {
        return app(FileAdderFactory::class)->createAllFromRequest($this);
    }

    /**
     * Add a remote file to the media library.
     *
     * @return FileAdder<TMedia>
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     */
    public function addMediaFromUrl(string $url, array|string ...$allowedMimeTypes): FileAdder
    {
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            throw InvalidUrl::doesNotStartWithProtocol($url);
        }

        $downloader = config('media-library.media_downloader', DefaultDownloader::class);
        $temporaryFile = (new $downloader)->getTempFile($url);
        $this->guardAgainstInvalidMimeType($temporaryFile, $allowedMimeTypes);

        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = urldecode($filename);

        if ($filename === '') {
            $filename = 'file';
        }

        if (! Str::contains($filename, '.')) {
            $mediaExtension = explode('/', mime_content_type($temporaryFile));
            $filename = "{$filename}.{$mediaExtension[1]}";
        }

        return app(FileAdderFactory::class)
            ->create($this, $temporaryFile)
            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
            ->usingFileName($filename);
    }

    /**
     * Add a file to the media library that contains the given string.
     *
     * @param string string
     * @return FileAdder<TMedia>
     */
    public function addMediaFromString(string $text): FileAdder
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($tmpFile, $text);

        $file = app(FileAdderFactory::class)
            ->create($this, $tmpFile)
            ->usingFileName('text.txt');

        return $file;
    }

    /**
     * Add a base64 encoded file to the media library.
     *
     * @return FileAdder<TMedia>
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     * @throws InvalidBase64Data
     */
    public function addMediaFromBase64(string $base64data, array|string ...$allowedMimeTypes): FileAdder
    {
        // strip out data uri scheme information (see RFC 2397)
        if (str_contains($base64data, ';base64')) {
            [$_, $base64data] = explode(';', $base64data);
            [$_, $base64data] = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        $binaryData = base64_decode($base64data, true);

        if ($binaryData === false) {
            throw InvalidBase64Data::create();
        }

        // decoding and then re-encoding should not change the data
        if (base64_encode($binaryData) !== $base64data) {
            throw InvalidBase64Data::create();
        }

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');
        file_put_contents($tmpFile, $binaryData);

        $this->guardAgainstInvalidMimeType($tmpFile, $allowedMimeTypes);

        $file = app(FileAdderFactory::class)->create($this, $tmpFile);

        return $file;
    }

    /**
     * Add a file to the media library from a stream.
     *
     * @return FileAdder<TMedia>
     */
    public function addMediaFromStream($stream): FileAdder
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($tmpFile, $stream);

        $file = app(FileAdderFactory::class)
            ->create($this, $tmpFile)
            ->usingFileName('text.txt');

        return $file;
    }

    /**
     * Copy a file to the media library.
     *
     * @return FileAdder<TMedia>
     */
    public function copyMedia(string|UploadedFile $file): FileAdder
    {
        return $this->addMedia($file)->preservingOriginal();
    }

    /*
     * Determine if there is media in the given collection.
     */
    public function hasMedia(string $collectionName = 'default', array|callable $filters = []): bool
    {
        return count($this->getMedia($collectionName, $filters)) ? true : false;
    }

    /**
     * Get media collection by its collectionName.
     *
     * @return MediaCollections\Models\Collections\MediaCollection<int, TMedia>
     */
    public function getMedia(string $collectionName = 'default', array|callable $filters = []): MediaCollections\Models\Collections\MediaCollection
    {
        return $this->getMediaRepository()
            ->getCollection($this, $collectionName, $filters)
            ->collectionName($collectionName);
    }

    public function getMediaRepository(): MediaRepository
    {
        return app(MediaRepository::class);
    }

    public function getMediaModel(): string
    {
        return config('media-library.media_model');
    }

    /**
     * @return TMedia|null
     */
    public function getFirstMedia(string $collectionName = 'default', $filters = []): ?Media
    {
        return $this->getMediaItem($collectionName, $filters, CollectionPosition::First);
    }

    /**
     * @return TMedia|null
     */
    public function getLastMedia(string $collectionName = 'default', $filters = []): ?Media
    {
        return $this->getMediaItem($collectionName, $filters, CollectionPosition::Last);
    }

    protected function getMediaItem(string $collectionName, $filters, CollectionPosition $position)
    {
        $media = $this->getMedia($collectionName, $filters);

        return $position === CollectionPosition::First
            ? $media->first()
            : $media->last();
    }

    private function getMediaItemUrl(string $collectionName, string $conversionName, CollectionPosition $position): string
    {
        $media = $position === CollectionPosition::First
            ? $this->getFirstMedia($collectionName)
            : $this->getLastMedia($collectionName);

        if (! $media) {
            return $this->getFallbackMediaUrl($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && ! $media->hasGeneratedConversion($conversionName)) {
            return $media->getUrl();
        }

        return $media->getUrl($conversionName);
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return $this->getMediaItemUrl($collectionName, $conversionName, CollectionPosition::First);
    }

    /*
     * Get the url of the image for the given conversionName
     * for last media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getLastMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return $this->getMediaItemUrl($collectionName, $conversionName, CollectionPosition::Last);
    }

    private function getMediaItemTemporaryUrl(
        DateTimeInterface $expiration,
        string $collectionName,
        string $conversionName,
        CollectionPosition $position
    ): string {
        $media = $position === CollectionPosition::First
            ? $this->getFirstMedia($collectionName)
            : $this->getLastMedia($collectionName);

        if (! $media) {
            return $this->getFallbackMediaUrl($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && ! $media->hasGeneratedConversion($conversionName)) {
            return $media->getTemporaryUrl($expiration);
        }

        return $media->getTemporaryUrl($expiration, $conversionName);
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     *
     * If no profile is given, return the source's url.
     */
    public function getFirstTemporaryUrl(
        ?DateTimeInterface $expiration = null,
        string $collectionName = 'default',
        string $conversionName = ''
    ): string {
        $expiration = $expiration ?: now()->addMinutes(config('media-library.temporary_url_default_lifetime'));

        return $this->getMediaItemTemporaryUrl($expiration, $collectionName, $conversionName, CollectionPosition::First);
    }

    /*
     * Get the url of the image for the given conversionName
     * for last media for the given collectionName.
     *
     * If no profile is given, return the source's url.
     */
    public function getLastTemporaryUrl(
        ?DateTimeInterface $expiration = null,
        string $collectionName = 'default',
        string $conversionName = ''
    ): string {
        $expiration = $expiration ?: now()->addMinutes(config('media-library.temporary_url_default_lifetime'));

        return $this->getMediaItemTemporaryUrl($expiration, $collectionName, $conversionName, CollectionPosition::Last);
    }

    public function getRegisteredMediaCollections(): Collection
    {
        $this->registerMediaCollections();

        return collect($this->mediaCollections);
    }

    public function getMediaCollection(string $collectionName = 'default'): ?MediaCollection
    {
        $this->registerMediaCollections();

        return collect($this->mediaCollections)
            ->first(fn (MediaCollection $collection) => $collection->name === $collectionName);
    }

    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $fallbackUrls = optional($this->getMediaCollection($collectionName))->fallbackUrls;

        if (in_array($conversionName, ['', 'default'], true)) {
            return $fallbackUrls['default'] ?? '';
        }

        return $fallbackUrls[$conversionName] ?? $fallbackUrls['default'] ?? '';
    }

    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        $fallbackPaths = optional($this->getMediaCollection($collectionName))->fallbackPaths;

        if (in_array($conversionName, ['', 'default'], true)) {
            return $fallbackPaths['default'] ?? '';
        }

        return $fallbackPaths[$conversionName] ?? $fallbackPaths['default'] ?? '';
    }

    private function getMediaItemPath(string $collectionName, string $conversionName, CollectionPosition $position): string
    {
        $media = $position === CollectionPosition::First
            ? $this->getFirstMedia($collectionName)
            : $this->getLastMedia($collectionName);

        if (! $media) {
            return $this->getFallbackMediaPath($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && ! $media->hasGeneratedConversion($conversionName)) {
            return $media->getPath();
        }

        return $media->getPath($conversionName);
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        return $this->getMediaItemPath($collectionName, $conversionName, CollectionPosition::First);
    }

    public function getLastMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        return $this->getMediaItemPath($collectionName, $conversionName, CollectionPosition::Last);
    }

    /*
     * Update a media collection by deleting and inserting again with new values.
     */
    public function updateMedia(array $newMediaArray, string $collectionName = 'default'): Collection
    {
        $this->removeMediaItemsNotPresentInArray($newMediaArray, $collectionName);

        $mediaClass = $this->getMediaModel();
        $mediaInstance = new $mediaClass;
        $keyName = $mediaInstance->getKeyName();

        return collect($newMediaArray)
            ->map(function (array $newMediaItem) use ($collectionName, $mediaClass, $keyName) {
                static $orderColumn = 1;

                $currentMedia = $mediaClass::findOrFail($newMediaItem[$keyName]);

                if ($currentMedia->collection_name !== $collectionName) {
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

    protected function removeMediaItemsNotPresentInArray(array $newMediaArray, string $collectionName = 'default'): void
    {
        $this
            ->getMedia($collectionName)
            ->reject(fn (Media $currentMediaItem) => in_array(
                $currentMediaItem->getKey(),
                array_column($newMediaArray, $currentMediaItem->getKeyName()),
            ))
            ->each(fn (Media $media) => $media->delete());

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }
    }

    /**
     * @return $this
     */
    public function clearMediaCollection(string $collectionName = 'default'): HasMedia
    {
        $this
            ->getMedia($collectionName)
            ->each(fn (Media $media) => $media->delete());

        event(new CollectionHasBeenClearedEvent($this, $collectionName));

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearMediaCollectionExcept(
        string $collectionName = 'default',
        array|Collection|Media $excludedMedia = []
    ): HasMedia {
        if ($excludedMedia instanceof Media) {
            $excludedMedia = collect()->push($excludedMedia);
        }

        $excludedMedia = collect($excludedMedia);

        if ($excludedMedia->isEmpty()) {
            return $this->clearMediaCollection($collectionName);
        }

        $this
            ->getMedia($collectionName)
            ->reject(fn (Media $media) => $excludedMedia->where($media->getKeyName(), $media->getKey())->count())
            ->each(fn (Media $media) => $media->delete());

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }

        if ($this->getMedia($collectionName)->isEmpty()) {
            event(new CollectionHasBeenClearedEvent($this, $collectionName));
        }

        return $this;
    }

    /**
     * Delete the associated media with the given id.
     * You may also pass a media object.
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted
     */
    public function deleteMedia(int|string|Media $mediaId): void
    {
        if ($mediaId instanceof Media) {
            $mediaId = $mediaId->getKey();
        }

        $media = $this->media->find($mediaId);

        if (! $media) {
            throw MediaCannotBeDeleted::doesNotBelongToModel($mediaId, $this);
        }

        $media->delete();
    }

    public function addMediaConversion(string $name): Conversion
    {
        $conversion = Conversion::create($name);

        $this->mediaConversions[] = $conversion;

        return $conversion;
    }

    public function addMediaCollection(string $name): MediaCollection
    {
        $mediaCollection = MediaCollection::create($name);

        $this->mediaCollections[$name] = $mediaCollection;

        return $mediaCollection;
    }

    public function deletePreservingMedia(): bool
    {
        $this->deletePreservingMedia = true;

        return $this->delete();
    }

    public function shouldDeletePreservingMedia(): bool
    {
        return $this->deletePreservingMedia ?? false;
    }

    protected function mediaIsPreloaded(): bool
    {
        return $this->relationLoaded('media');
    }

    public function loadMedia(string $collectionName): Collection
    {
        if (config('media-library.force_lazy_loading') && $this->exists) {
            $this->loadMissing('media');
        }

        $collection = $this->exists
            ? $this->media
            : collect($this->unAttachedMediaLibraryItems)->pluck('media');

        $collection = new MediaCollections\Models\Collections\MediaCollection($collection);

        return $collection
            ->filter(fn (Media $mediaItem) => $collectionName !== '*' ? $mediaItem->collection_name === $collectionName : true)
            ->sortBy('order_column')
            ->values();
    }

    public function prepareToAttachMedia(Media $media, FileAdder $fileAdder): void
    {
        $this->unAttachedMediaLibraryItems[] = compact('media', 'fileAdder');
    }

    public function processUnattachedMedia(callable $callable): void
    {
        foreach ($this->unAttachedMediaLibraryItems as $item) {
            $callable($item['media'], $item['fileAdder']);
        }

        $this->unAttachedMediaLibraryItems = [];
    }

    protected function guardAgainstInvalidMimeType(string $file, ...$allowedMimeTypes): void
    {
        $allowedMimeTypes = Arr::flatten($allowedMimeTypes);

        if (empty($allowedMimeTypes)) {
            return;
        }

        $validation = Validator::make(
            ['file' => new File($file)],
            ['file' => 'mimetypes:'.implode(',', $allowedMimeTypes)]
        );

        if ($validation->fails()) {
            throw MimeTypeNotAllowed::create($file, $allowedMimeTypes);
        }
    }

    public function deleteAllMedia(): self
    {
        $this
            ->media()
            ->cursor()
            ->each(fn (Media $media) => $media->delete());

        return $this;
    }

    public function registerMediaConversions(?Media $media = null): void {}

    public function registerMediaCollections(): void {}

    public function registerAllMediaConversions(?Media $media = null): void
    {
        $this->registerMediaCollections();

        collect($this->mediaCollections)->each(function (MediaCollection $mediaCollection) use ($media) {
            $actualMediaConversions = $this->mediaConversions;

            $this->mediaConversions = [];

            ($mediaCollection->mediaConversionRegistrations)($media);

            $preparedMediaConversions = collect($this->mediaConversions)
                ->each(fn (Conversion $conversion) => $conversion->performOnCollections($mediaCollection->name))
                ->values()
                ->toArray();

            $this->mediaConversions = [...$actualMediaConversions, ...$preparedMediaConversions];
        });

        $this->registerMediaConversions($media);
    }

    public function __sleep(): array
    {
        // do not serialize properties from the trait
        return collect(parent::__sleep())
            ->reject(
                fn ($key) => in_array(
                    $key,
                    [
                        'mediaConversions',
                        'mediaCollections',
                        'unAttachedMediaLibraryItems',
                        'deletePreservingMedia',
                    ]
                )
            )->toArray();
    }
}
