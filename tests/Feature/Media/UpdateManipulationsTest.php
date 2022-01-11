<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

uses(TestCase::class);

it('will create derived files when manipulations have changed', function () {
    $testModelClass = new class () extends TestModel {
        public function registerMediaConversions(Media $media = null) {
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
});

it('will not create derived files when manipulations have not changed', function () {
    $testModelClass = new class () extends TestModel {
        public function registerMediaConversions(Media $media = null) {
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
});
