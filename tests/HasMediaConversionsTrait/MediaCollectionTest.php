<?php

namespace Spatie\MediaLibrary\Test\HasMediaConversionsTrait;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModelWithConversion;

class MediaCollectionTest extends TestCase
{
    /** @test */
    public function it_will_use_the_disk_from_a_media_collection()
    {
        $testModel = new class extends TestModelWithConversion
        {
            public function registerMediaConversions(Media $media = null)
            {

            }

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
            public function registerMediaConversions(Media $media = null)
            {

            }

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
}