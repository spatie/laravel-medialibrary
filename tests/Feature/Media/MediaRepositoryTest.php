<?php

use Programic\MediaLibrary\MediaCollections\MediaRepository;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;

it('can use a custom media model', function () {
    config()->set('media-library.media_model', TestCustomMediaModel::class);

    $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $mediaRepository = app(MediaRepository::class);

    expect($mediaRepository->getCollection($this->testModel, 'default')->getQueueableClass())->toEqual(TestCustomMediaModel::class);
});
