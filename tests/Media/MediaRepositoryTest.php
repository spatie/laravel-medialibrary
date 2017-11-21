<?php

namespace Spatie\MediaLibrary\Tests\Media;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Tests\TestCustomMediaModel;

class MediaRepositoryTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('medialibrary.media_model', TestCustomMediaModel::class);
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
