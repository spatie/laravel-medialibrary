<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class EventTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();

        Event::fake();
    }

    /** @test */
    public function it_will_fire_the_media_added_event()
    {
        $this->addMediaTo($this->testModel);

        Event::assertDispatched(MediaHasBeenAdded::class);
    }
    
    private function addMediaTo(TestModel $testModel): void
    {
        $testModel->addMedia($this->getTestJpg())->toMediaCollection();
    }
}
