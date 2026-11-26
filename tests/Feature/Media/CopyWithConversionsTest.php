<?php

use Illuminate\Support\Facades\Queue;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionQueued;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithResponsiveImages;

it('copies the generated conversions instead of generating them again', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    file_put_contents($media->getPath('thumb'), 'already generated');

    $anotherModel = TestModelWithConversion::create(['name' => 'another-test']);

    $copiedMedia = $media->copyWithConversions($anotherModel);

    expect($anotherModel->getMedia())->toHaveCount(1);
    expect($copiedMedia->getPath())->toBeFile();
    expect($copiedMedia->hasGeneratedConversion('thumb'))->toBeTrue();
    expect(file_get_contents($copiedMedia->getPath('thumb')))->toBe('already generated');
    expect($copiedMedia->getPath('keep_original_format'))->toBeFile();
});

it('copies the responsive images without queueing a job', function () {
    $testModel = new class extends TestModelWithResponsiveImages
    {
        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('images')->withResponsiveImages();
        }
    };

    /** @var Media $media */
    $media = $testModel::create(['name' => 'test'])
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('images');

    $media->refresh();

    $anotherModel = $testModel::create(['name' => 'another-test']);

    Queue::fake();

    $copiedMedia = $media->copyWithConversions($anotherModel, 'images');

    Queue::assertNothingPushed();

    expect($copiedMedia->responsive_images)->toEqual($media->responsive_images);

    foreach (['', 'thumb'] as $conversionName) {
        expect($copiedMedia->responsiveImages($conversionName)->getFilenames())->not->toBeEmpty();

        foreach ($copiedMedia->responsiveImages($conversionName)->getFilenames() as $fileName) {
            $this->assertFileExists($this->getMediaDirectory("{$copiedMedia->id}/responsive-images/{$fileName}"));
        }
    }
});

it('does not copy responsive images the model does not generate', function () {
    /** @var Media $media */
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media->refresh();

    file_put_contents($media->getPath('thumb'), 'already generated');

    $copiedMedia = $media->copyWithConversions(TestModelWithConversion::create(['name' => 'another-test']));

    expect(file_get_contents($copiedMedia->getPath('thumb')))->toBe('already generated');
    expect($copiedMedia->refresh()->responsive_images)->toBeEmpty();
});

it('does not queue the conversions that were copied', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $media->refresh();

    $anotherModel = TestModelWithConversionQueued::create(['name' => 'another-test']);

    Queue::fake();

    $media->copyWithConversions($anotherModel);

    Queue::assertNothingPushed();
});

it('generates the conversions the original does not have', function () {
    /** @var Media $media */
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $copiedMedia = $media->copyWithConversions($this->testModelWithConversion);

    expect($copiedMedia->hasGeneratedConversion('thumb'))->toBeTrue();
    expect($copiedMedia->getPath('thumb'))->toBeFile();
});

it('only generates the conversions the original has not generated yet', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $media->refresh()->markAsConversionNotGenerated('keep_original_format');

    file_put_contents($media->getPath('thumb'), 'already generated');

    $anotherModel = TestModelWithConversionQueued::create(['name' => 'another-test']);

    $copiedMedia = $media->copyWithConversions($anotherModel)->refresh();

    expect(file_get_contents($copiedMedia->getPath('thumb')))->toBe('already generated');
    expect($copiedMedia->hasGeneratedConversion('keep_original_format'))->toBeTrue();
    expect($copiedMedia->getPath('keep_original_format'))->toBeFile();
});

it('generates the conversions again when copying to another disk', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    file_put_contents($media->getPath('thumb'), 'already generated');

    $anotherModel = TestModelWithConversion::create(['name' => 'another-test']);

    $copiedMedia = $media->copyWithConversions($anotherModel, 'default', 'secondMediaDisk');

    expect($copiedMedia->conversions_disk)->toBe('secondMediaDisk');
    expect($copiedMedia->getPath('thumb'))->toBeFile();
    expect(file_get_contents($copiedMedia->getPath('thumb')))->not->toBe('already generated');
});

it('names the copied conversions after the new file name', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    file_put_contents($media->getPath('thumb'), 'already generated');

    $anotherModel = TestModelWithConversion::create(['name' => 'another-test']);

    $copiedMedia = $media->copyWithConversions($anotherModel, fileName: 'new-name.jpg');

    expect(file_get_contents($this->getMediaDirectory("{$copiedMedia->id}/conversions/new-name-thumb.jpg")))->toBe('already generated');
});

it('generates a conversion again when the model defines it with another format', function () {
    $testModel = new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this->addMediaConversion('thumb')->width(50)->format('png')->nonQueued();
        }
    };

    /** @var Media $media */
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $copiedMedia = $media->copyWithConversions($testModel::create(['name' => 'another-test']));

    expect($copiedMedia->getPath('thumb'))->toHaveExtension('png');
    expect(mime_content_type($copiedMedia->getPath('thumb')))->toBe('image/png');
});

it('generates a conversion again when its file cannot be copied', function () {
    /** @var Media $media */
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    unlink($media->getPath('thumb'));

    $copiedMedia = $media->copyWithConversions(TestModelWithConversion::create(['name' => 'another-test']));

    expect($copiedMedia->refresh()->hasGeneratedConversion('thumb'))->toBeTrue();
    expect($copiedMedia->getPath('thumb'))->toBeFile();
});

it('generates the responsive images of a renamed copy again so they are removed with it', function () {
    $testModel = new class extends TestModel
    {
        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('images')->withResponsiveImages();
        }
    };

    /** @var Media $media */
    $media = $testModel::create(['name' => 'test'])
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('images');

    $media->refresh();

    $copiedMedia = $media->copyWithConversions($testModel::create(['name' => 'another-test']), 'images', fileName: 'new-name.jpg');

    $copiedMedia->refresh();

    expect($copiedMedia->responsiveImages()->getFilenames())->not->toBeEmpty();

    foreach ($copiedMedia->responsiveImages()->getFilenames() as $fileName) {
        expect($fileName)->toStartWith('new-name___');
    }

    $copiedMedia->delete();

    expect($this->getMediaDirectory((string) $copiedMedia->id))->not->toBeDirectory();
});
