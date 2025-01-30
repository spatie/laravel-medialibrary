<?php

use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

it('calling getRegisteredMediaCollections multiple times should return the same result', function () {
    $testModel = new class extends TestModelWithConversion
    {
        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('images')
                ->useDisk('secondMediaDisk');
        }
    };

    $result = $testModel->getRegisteredMediaCollections();
    ray($result);

    expect($result)->toHaveCount(1);

    $result = $testModel->getRegisteredMediaCollections();
    ray($result);

    expect($result)->toHaveCount(1);
});
