<?php

namespace Spatie\MediaLibrary;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibraryPro\PendingMediaLibraryRequestHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property bool $registerMediaConversionsUsingModelInstance
 * @property ?\Spatie\MediaLibrary\MediaCollections\MediaCollection $mediaCollections
 */
interface HasMedia
{
    public function media(): MorphMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function addMediaFromRequest(string $key): FileAdder;

    public function addMediaFromDisk(string $key, string $disk = null): FileAdder;

    public function addFromMediaLibraryRequest(?array $mediaLibraryRequestItems): PendingMediaLibraryRequestHandler;

    public function syncFromMediaLibraryRequest(?array $mediaLibraryRequestItems): PendingMediaLibraryRequestHandler;

    public function addMultipleMediaFromRequest(array $keys): Collection;

    public function addAllMediaFromRequest(): Collection;

    public function addMediaFromUrl(string $url, array|string ...$allowedMimeTypes): FileAdder;

    public function addMediaFromString(string $text): FileAdder;

    public function addMediaFromBase64(string $base64data, array|string ...$allowedMimeTypes): FileAdder;

    public function addMediaFromStream($stream): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function getMediaRepository(): MediaRepository;

    public function getFirstMedia(string $collectionName = 'default', $filters = []): ?Media;

    public function getFirstMediaUrl(string $collectionName = 'default', string $conversionName = ''): string;

    public function getFirstTemporaryUrl(
        DateTimeInterface $expiration,
        string $collectionName = 'default',
        string $conversionName = ''
    ): string;

    public function getRegisteredMediaCollections(): Collection;

    public function getMediaCollection(string $collectionName = 'default'): ?MediaCollection;

    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string;

    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string;

    public function getFirstMediaPath(string $collectionName = 'default', string $conversionName = ''): string;

    public function updateMedia(array $newMediaArray, string $collectionName = 'default'): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasMedia;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMedia;

    public function deleteMedia(int|string|Media $mediaId): void;
    public function addMediaConversion(string $name): Conversion;

    public function addMediaCollection(string $name): MediaCollection;

    public function deletePreservingMedia(): bool;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function prepareToAttachMedia(Media $media, FileAdder $fileAdder): void;

    public function processUnattachedMedia(callable $callable): void;

    public function registerMediaConversions(Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;
}
