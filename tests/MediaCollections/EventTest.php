<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenDeleted;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class EventTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();
    }

    /** @test */
    public function it_will_fire_the_media_added_event()
    {
        Event::fake();

        $this->addMediaTo($this->testModel);

        Event::assertDispatched(MediaHasBeenAdded::class);
    }
    
    /** @test */
    public function it_will_fire_the_media_deleted_event_when_a_media_entity_is_deleted()
    {
        Event::fake([
            MediaHasBeenDeleted::class
        ]);

        $this->addMediaTo($this->testModel);
        $media = $this->testModel->getMedia()->first();

        $media->delete();

        Event::assertDispatched(MediaHasBeenDeleted::class, function (MediaHasBeenDeleted $event) use ($media) {
            return $event->media->id === $media->id
                && $event->media->getPath() === $media->getPath();
        });
    }

    private function addMediaTo(TestModel $testModel): void
    {
        $testModel->addMedia($this->getTestJpg())->toMediaCollection();
    }
}
