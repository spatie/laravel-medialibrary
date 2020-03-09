<?php

namespace Spatie\MediaLibrary\Tests\Conversions;

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Conversions\Events\ConversionWillStart;
use Spatie\MediaLibrary\MediaCollections\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Tests\TestCase;

class EventTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();

        Event::fake();
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
