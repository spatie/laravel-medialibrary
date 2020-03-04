<?php

namespace Spatie\Medialibrary\Tests\MediaCollections;

use Illuminate\Support\Facades\Event;
use Spatie\Medialibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\Medialibrary\Tests\TestCase;

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
        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        Event::assertDispatched(MediaHasBeenAdded::class);
    }
}
