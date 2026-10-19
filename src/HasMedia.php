<?php

namespace Spatie\MediaLibrary;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin Model
 *
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 * @method FileAdder addMediaFromRequest(string $key)
 * @method FileAdder addMediaFromDisk(string $key, ?string $disk = null)
 * @method FileAdder addMediaFromUrl(string $url, array|string ...$allowedMimeTypes)
 * @method FileAdder addMediaFromString(string $text)
 * @method FileAdder addMediaFromBase64(string $base64data, array|string ...$allowedMimeTypes)
 * @method FileAdder addMediaFromStream(mixed $stream)
 * @method Collection addMultipleMediaFromRequest(array $keys)
 * @method Collection addAllMediaFromRequest()
 * @method ?Media getFirstMedia(string $collectionName = 'default', array|callable $filters = [])
 * @method ?Media getLastMedia(string $collectionName = 'default', array|callable $filters = [])
 * @method string getFirstMediaUrl(string $collectionName = 'default', string $conversionName = '')
 * @method string getLastMediaUrl(string $collectionName = 'default', string $conversionName = '')
 * @method string getFirstTemporaryUrl(?DateTimeInterface $expiration = null, string $collectionName = 'default', string $conversionName = '')
 * @method string getLastTemporaryUrl(?DateTimeInterface $expiration = null, string $collectionName = 'default', string $conversionName = '')
 * @method string getFirstMediaPath(string $collectionName = 'default', string $conversionName = '')
 * @method string getLastMediaPath(string $collectionName = 'default', string $conversionName = '')
 * @method string getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = '')
 * @method string getFallbackMediaPath(string $collectionName = 'default', string $conversionName = '')
 * @method Collection getRegisteredMediaCollections()
 * @method MediaCollection addMediaCollection(string $name)
 * @method Collection updateMedia(array $newMediaArray, string $collectionName = 'default')
 * @method void deleteMedia(int|string|Media $mediaId)
 * @method static deleteAllMedia()
 * @method bool deletePreservingMedia()
 *
 * @property bool $registerMediaConversionsUsingModelInstance
 * @property ?MediaCollection $mediaCollections
 */
interface HasMedia
{
    public function media(): MorphMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasMedia;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMedia;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(?Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;

    public function getMediaCollection(string $collectionName = 'default'): ?MediaCollection;

    public function getMediaModel(): string;
}
