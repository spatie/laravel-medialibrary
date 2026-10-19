<?php

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('generates differently named files for same-named conversions in separate collections', function () {
    $model = ScopedAttributeModel::create(['name' => 'scoped']);

    $portrait = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('portraits');
    $landscape = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('landscapes');

    expect(getimagesize($portrait->getPath('thumb')))->toMatchArray([0 => 31, 1 => 47, 'mime' => 'image/webp'])
        ->and(getimagesize($landscape->getPath('thumb')))->toMatchArray([0 => 73, 1 => 29, 'mime' => 'image/png']);
});

it('lets a method override an attribute on overlapping collections without generating both files', function () {
    $model = OverlappingAttributeModel::create(['name' => 'overlap']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('portraits');

    expect($media->getPath('thumb'))->toEndWith('.png')
        ->and(getimagesize($media->getPath('thumb'))[0])->toBe(19)
        ->and(glob(dirname($media->getPath('thumb')).'/*'))->toHaveCount(1);
});

it('applies attribute disk, mime, retention, fallback and responsive configuration to stored media', function () {
    $model = ConfiguredAttributeModel::create(['name' => 'configured']);

    expect($model->getFirstMediaUrl('photos'))->toBe('/missing.jpg')
        ->and($model->getFirstMediaPath('photos'))->toBe('/missing/path.jpg');

    expect(fn () => $model->addMedia($this->getTestPng())->preservingOriginal()->toMediaCollection('photos'))
        ->toThrow(FileUnacceptableForCollection::class);

    $first = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('photos');
    $second = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('photos');
    $third = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('photos');

    expect($model->getMedia('photos')->modelKeys())->toBe([$second->id, $third->id])
        ->and($first->fresh())->toBeNull()
        ->and($third->disk)->toBe('secondMediaDisk')
        ->and($third->conversions_disk)->toBe('public')
        ->and($third->fresh()->hasResponsiveImages())->toBeTrue()
        ->and(getimagesize($third->getPath('small'))[0])->toBe(53)
        ->and($third->getUrl('small'))->toStartWith('/media/');
});

#[MediaCollection(name: 'photos', singleFile: true, onlyKeepLatest: 2, acceptsMimeTypes: ['image/jpeg'], disk: 'secondMediaDisk', conversionsDisk: 'public', fallbackUrl: '/missing.jpg', fallbackPath: '/missing/path.jpg', responsiveImages: true)]
#[MediaConversion(name: 'small', collections: ['photos'], width: 53, queued: false)]
class ConfiguredAttributeModel extends TestModel {}

#[MediaCollection(name: 'portraits')]
#[MediaCollection(name: 'landscapes')]
#[MediaConversion(name: 'thumb', width: 101, format: 'jpg', queued: false)]
#[MediaConversion(name: 'thumb', collections: ['portraits'], width: 31, height: 47, fit: Fit::Crop, format: 'webp', queued: false)]
#[MediaConversion(name: 'thumb', collections: ['landscapes'], width: 73, height: 29, fit: Fit::Crop, format: 'png', queued: false)]
class ScopedAttributeModel extends TestModel {}

class OverlappingAttributeModel extends ScopedAttributeModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(19)->format('png')->nonQueued();
    }
}
