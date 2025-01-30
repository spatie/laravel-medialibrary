<?php

use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('will create derived files when manipulations have changed', function () {
    $testModelClass = new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this->addMediaConversion('update_test');
        }
    };

    $testModel = $testModelClass::find($this->testModel->id);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    touch($media->getPath('update_test'), time() - 1);

    $conversionModificationTime = filemtime($media->getPath('update_test'));

    $media->manipulations = [
        'update_test' => [
            'width' => [1],
            'height' => [1],
        ],
    ];

    $media->save();

    $modificationTimeAfterManipulationChanged = filemtime($media->getPath('update_test'));

    expect($modificationTimeAfterManipulationChanged)->toBeGreaterThan($conversionModificationTime);
});

it('will not create derived files when manipulations have not changed', function () {
    $testModelClass = new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this->addMediaConversion('update_test');
        }
    };

    $testModel = $testModelClass::find($this->testModel->id);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    $media->manipulations = [
        'update_test' => [
            'width' => [1],
            'height' => [1],
        ],
    ];

    $media->save();

    touch($media->getPath('update_test'), time() - 1);

    $conversionModificationTime = filemtime($media->getPath('update_test'));

    $media->manipulations = [
        'update_test' => [
            'width' => [1],
            'height' => [1],
        ],
    ];

    $media->updated_at = now()->addSecond();

    $media->save();

    $modificationTimeAfterManipulationChanged = filemtime($media->getPath('update_test'));

    expect($modificationTimeAfterManipulationChanged)->toEqual($conversionModificationTime);
});
