<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class UpdateManipulationsTest extends TestCase
{
    /** @test */
    public function it_will_create_derived_files_when_manipulations_have_changed()
    {
        $testModelClass = new class() extends TestModel {
            public function registerMediaConversions(Media $media = null): void
            {
                $this->addMediaConversion('update_test');
            }
        };

        $testModel = $testModelClass::find($this->testModel->id);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        touch($media->getPath('update_test'), time() - 1);

        $conversionModificationTime = filemtime($media->getPath('update_test'));

        $media->manipulations = [
            'update_test' => [
                'width' => 1,
                'height' => 1,
            ],
        ];

        $media->save();

        $modificationTimeAfterManipulationChanged = filemtime($media->getPath('update_test'));

        $this->assertGreaterThan($conversionModificationTime, $modificationTimeAfterManipulationChanged);
    }

    /** @test */
    public function it_will_not_create_derived_files_when_manipulations_have_not_changed()
    {
        $testModelClass = new class() extends TestModel {
            public function registerMediaConversions(Media $media = null): void
            {
                $this->addMediaConversion('update_test');
            }
        };

        $testModel = $testModelClass::find($this->testModel->id);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $media->manipulations = [
            'update_test' => [
                'width' => 1,
                'height' => 1,
            ], ];

        $media->save();

        touch($media->getPath('update_test'), time() - 1);

        $conversionModificationTime = filemtime($media->getPath('update_test'));

        $media->manipulations = [
            'update_test' => [
                'width' => 1,
                'height' => 1,
            ], ];

        $media->updated_at = now()->addSecond();

        $media->save();

        $modificationTimeAfterManipulationChanged = filemtime($media->getPath('update_test'));

        $this->assertEquals($conversionModificationTime, $modificationTimeAfterManipulationChanged);
    }
}
