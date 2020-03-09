<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;

class MediaRepositoryTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('media-library.media_model', TestCustomMediaModel::class);
    }

    /** @test */
    public function it_can_use_a_custom_media_model()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $mediaRepository = app(MediaRepository::class);

        $this->assertEquals(TestCustomMediaModel::class, $mediaRepository->all()->getQueueableClass());
    }
}
