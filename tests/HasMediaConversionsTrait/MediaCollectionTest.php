<?php

namespace Spatie\MediaLibrary\Tests\HasMediaConversionsTrait;

use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestModelWithoutMediaConversions;

class MediaCollectionTest extends TestCase
{
    /** @test */
    public function it_will_use_the_disk_from_a_media_collection()
    {
        $testModel = new class extends TestModelWithConversion
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')
                    ->disk('secondMediaDisk');
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $this->assertFileNotExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('other-images');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');
    }

    /** @test */
    public function it_will_not_use_the_disk_name_of_the_collection_if_a_diskname_is_specified_while_adding()
    {
        $testModel = new class extends TestModelWithConversion
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')
                    ->disk('secondMediaDisk');
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

        $this->assertFileNotExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    }

    /** @test */
    public function it_can_register_media_conversions_when_defining_media_collections()
    {
        $testModel = new class extends TestModelWithoutMediaConversions
        {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function(Media $media) {
                        $this
                            ->addMediaConversion('thumb')
                            ->greyscale();
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
    }

    /** @test */
    public function it_will_not_use_media_conversions_from_an_unrelated_collection()
    {
        $testModel = new class extends TestModelWithoutMediaConversions
        {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function(Media $media) {
                        $this
                            ->addMediaConversion('thumb')
                            ->greyscale();
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('unrelated-collection');

        $this->assertFileNotExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
    }

    /** @test */
    public function it_will_use_conversions_defined_in_conversions_and_conversions_defined_in_collections()
    {
        $testModel = new class extends TestModelWithoutMediaConversions
        {
            public function registerMediaConversions(Media $media = null)
            {
                $this
                    ->addMediaConversion('another-thumb')
                    ->greyscale();
            }

            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function(Media $media = null) {
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
    }
}