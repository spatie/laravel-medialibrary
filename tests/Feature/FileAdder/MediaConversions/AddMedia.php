<?php

namespace Spatie\MediaLibrary\Tests\Feature\FileAdder\MediaConversions;

use Carbon\Carbon;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion;

class AddMedia extends TestCase
{
    /** @test */
    public function it_can_add_an_file_to_the_default_collection()
    {
        $media = $this->testModelWithoutMediaConversions
            ->copyMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection();

        $this->assertEquals('default', $media->collection_name);
    }

    /** @test */
    public function it_can_create_a_derived_version_of_an_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));
    }

    /** @test */
    public function it_will_not_create_a_derived_version_for_non_registered_collections()
    {
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection('downloads');

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));
    }

    /** @test */
    public function it_will_create_a_derived_version_for_an_image_without_an_extension()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('image'))
            ->toMediaCollection('images');

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/image-thumb.jpg'));
    }

    /** @test */
    public function it_can_create_a_derived_version_for_an_image_keeping_the_original_format()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestPng())
            ->toMediaCollection('images');

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-keep_original_format.png'));
    }

    /** @test */
    public function it_will_use_the_name_of_the_conversion_for_naming_the_converted_file()
    {
        $modelClass = new class() extends TestModelWithConversion {
            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('my-conversion')
                    ->setManipulations(function (Manipulations $manipulations) {
                        $manipulations
                            ->removeManipulation('format');
                    })
                    ->nonQueued();
            }
        };

        $model = $modelClass::first();

        $media = $model
            ->addMedia($this->getTestFilesDirectory('test.png'))
            ->toMediaCollection('images');

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-my-conversion.png'));
    }

    /** @test */
    public function it_can_create_a_derived_version_of_a_pdf_if_imagick_exists()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.pdf'))
            ->toMediaCollection('images');

        $thumbPath = $this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg');

        class_exists('Imagick') ? $this->assertFileExists($thumbPath) : $this->assertFileNotExists($thumbPath);
    }

    /** @test */
    public function it_will_not_create_a_derived_version_if_manipulations_did_not_change()
    {
        Carbon::setTestNow();

        $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

        $originalThumbCreatedAt = filemtime($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));

        Carbon::setTestNow(Carbon::now()->addMinute());

        $media->order_column += 1;
        $media->save();

        $thumbsCreatedAt = filemtime($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));

        $this->assertEquals($originalThumbCreatedAt, $thumbsCreatedAt);
    }

    /** @test */
    public function it_will_have_access_the_model_instance_when_registerMediaConversionsUsingModelInstance_has_been_set()
    {
        $modelClass = new class extends TestModel {
            public $registerMediaConversionsUsingModelInstance = true;

            /**
             * Register the conversions that should be performed.
             *
             * @return array
             */
            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')
                    ->width($this->width)
                    ->nonQueued();
            }
        };

        $model = new $modelClass;
        $model->name = 'testmodel';
        $model->width = 123;
        $model->save();

        $media = $model
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $conversionCollection = ConversionCollection::createForMedia($media);

        $conversion = $conversionCollection->getConversions()[0];

        $conversionManipulations = $conversion
            ->getManipulations()
            ->getManipulationSequence()
            ->toArray()[0];

        $this->assertEquals(123, $conversionManipulations['width']);
    }
}
