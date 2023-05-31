<?php

namespace Spatie\MediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface HasManyMedia
{
    public function media(): MorphToMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasManyMedia;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasManyMedia;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;
}
