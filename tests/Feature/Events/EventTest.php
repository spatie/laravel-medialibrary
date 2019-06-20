<?php

namespace Spatie\MediaLibrary\Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Events\ConversionWillStart;
use Spatie\MediaLibrary\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;

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

    /** @test */
    public function it_will_fire_the_conversion_will_start_event()
    {
        $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

        Event::assertDispatched(ConversionWillStart::class);
    }

    /** @test */
    public function it_will_fire_the_conversion_complete_event()
    {
        $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

        Event::assertDispatched(ConversionHasBeenCompleted::class);
    }

    /** @test */
    public function it_will_fire_the_collection_cleared_event()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');

        $this->testModel->clearMediaCollection('images');

        Event::assertDispatched(CollectionHasBeenCleared::class);
    }
}
