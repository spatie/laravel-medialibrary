<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithoutMediaConversions;

uses(TestCase::class);

it('will use the disk from a media collection', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this->addMediaCollection('images')
                ->useDisk('secondMediaDisk');
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->assertFileDoesNotExist($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection('other-images');

    $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');
});

it('will not use the disk name of the collection if a diskname is specified while adding', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this->addMediaCollection('images')
                ->useDisk('secondMediaDisk');
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

    $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

    $this->assertFileDoesNotExist($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
});

it('can register media conversions when defining media collections', function () {
    $testModel = new class () extends TestModelWithoutMediaConversions {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->registerMediaConversions(function (Media $media) {
                    $this
                        ->addMediaConversion('thumb')
                        ->greyscale();
                });
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

    $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
});

it('will not use media conversions from an unrelated collection', function () {
    $testModel = new class () extends TestModelWithoutMediaConversions {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->registerMediaConversions(function (Media $media) {
                    $this
                        ->addMediaConversion('thumb')
                        ->greyscale();
                });
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection('unrelated-collection');

    $this->assertFileDoesNotExist($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
});

it('will use conversions defined in conversions and conversions defined in collections', function () {
    $testModel = new class () extends TestModelWithoutMediaConversions {
        public function registerMediaConversions(Media $media = null) {
            $this
                ->addMediaConversion('another-thumb')
                ->greyscale();
        }

        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->registerMediaConversions(function (Media $media = null) {
                    $this
                        ->addMediaConversion('thumb')
                        ->greyscale();
                });
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

    $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');

    $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-another-thumb.jpg');
});

it('can accept certain files', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->acceptsFile(fn (File $file) => $file->mimeType === 'image/jpeg');
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->expectException(FileUnacceptableForCollection::class);

    $model->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection('images');
});

it('can guard against invalid mimetypes', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->acceptsMimeTypes(['image/jpeg']);
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->expectException(FileUnacceptableForCollection::class);

    $model->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection('images');
});

it('can generate responsive images', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->withResponsiveImages();
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $media = $model->getMedia('images')->first();

    $this->assertEquals([
        'http://localhost/media/1/responsive-images/test___media_library_original_340_280.jpg',
        'http://localhost/media/1/responsive-images/test___media_library_original_284_233.jpg',
        'http://localhost/media/1/responsive-images/test___media_library_original_237_195.jpg',
    ], $media->getResponsiveImageUrls());

    $this->assertEquals([], $media->getResponsiveImageUrls('non-existing-conversion'));
});

it('can generate responsive images on condition', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->withResponsiveImagesIf(true);
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $media = $model->getMedia('images')->first();

    $this->assertEquals([
        'http://localhost/media/1/responsive-images/test___media_library_original_340_280.jpg',
        'http://localhost/media/1/responsive-images/test___media_library_original_284_233.jpg',
        'http://localhost/media/1/responsive-images/test___media_library_original_237_195.jpg',
    ], $media->getResponsiveImageUrls());

    $this->assertEquals([], $media->getResponsiveImageUrls('non-existing-conversion'));
});

test('if the single file method is specified it will delete all other media and will only keep the new one', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->singleFile();
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->assertCount(1, $model->getMedia('images'));
});

test('if the only keeps latest method is specified it will delete all other media and will only keep the latest n ones', function () {
    $testModel = new class () extends TestModelWithConversion {
        public function registerMediaCollections() {
            $this
                ->addMediaCollection('images')
                ->onlyKeepLatest(3);
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);

    $firstFile = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->assertFalse($model->getMedia('images')->contains(fn ($model) => $model->is($firstFile)));
    $this->assertCount(3, $model->getMedia('images'));
});
