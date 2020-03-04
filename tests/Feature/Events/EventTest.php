<?php

namespace Spatie\Medialibrary\Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use Spatie\Medialibrary\Features\MediaCollections\Events\CollectionHasBeenCleared;
use Spatie\Medialibrary\Features\Conversions\Events\ConversionHasBeenCompleted;
use Spatie\Medialibrary\Features\Conversions\Events\ConversionWillStart;
use Spatie\Medialibrary\Features\MediaCollections\Events\MediaHasBeenAdded;
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
