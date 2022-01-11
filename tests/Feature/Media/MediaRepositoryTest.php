<?php

use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;


it('can use a custom media model', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $mediaRepository = app(MediaRepository::class);

    expect($mediaRepository->all()->getQueueableClass())->toEqual(TestCustomMediaModel::class);
});

// Helpers
function getEnvironmentSetUp($app)
{
    parent::getEnvironmentSetUp($app);

    $app['config']->set('media-library.media_model', TestCustomMediaModel::class);
}
