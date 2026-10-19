<?php

use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

beforeEach(fn () => config()->set('media-library.image_drivers.cloudflare-delivery.zone', 'https://images.test'));

it('does not reuse virtual conversion names between media of the same model', function () {
    $model = DynamicVirtualModel::create(['name' => 'dynamic']);
    $plain = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect($plain->hasGeneratedConversion('hero'))->toBeFalse();

    $virtual = $model->addMedia($this->getTestJpg())->preservingOriginal()
        ->withCustomProperties(['virtual' => true])->toMediaCollection();

    expect($virtual->hasGeneratedConversion('hero'))->toBeTrue()
        ->and($virtual->getUrl('hero'))->toStartWith('https://images.test/cdn-cgi/image/');
});

it('honors collection scoping and changes to the media when resolving virtual urls', function () {
    $model = DynamicVirtualModel::create(['name' => 'dynamic']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()
        ->withCustomProperties(['virtual' => true])->toMediaCollection('other');

    expect($media->hasGeneratedConversion('hero'))->toBeFalse();

    $media->collection_name = 'default';
    $media->save();

    expect($media->hasGeneratedConversion('hero'))->toBeTrue();

    $media->setCustomProperty('virtual', false)->save();

    expect($media->hasGeneratedConversion('hero'))->toBeFalse();
});

class DynamicVirtualModel extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        if (! $media?->getCustomProperty('virtual')) {
            return;
        }

        $this->addMediaConversion('hero')->performOnCollections('default')
            ->useImageDriver('cloudflare-delivery')
            ->manipulate(static fn (CloudflareImage $image) => $image->width(83));
    }
}
