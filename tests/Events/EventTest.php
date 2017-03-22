<?php

namespace Spatie\MediaLibrary\Test\Events;

use Event;
use Exception;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;

class EventTest extends TestCase
{
    protected $firedEvents = [];

    public function setUp()
    {
        parent::setup();

        $this->firedEvents = [];
    }

    /** @test */
    public function it_will_fire_the_media_added_event()
    {
        $this->expectsEvent(MediaHasBeenAdded::class);

        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_will_fire_the_conversion_complete_event()
    {
        $this->expectsEvent(ConversionHasBeenCompleted::class);

        $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_will_fire_the_collection_cleared_event()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');

        $this->expectsEvent(CollectionHasBeenCleared::class);

        $this->testModel->clearMediaCollection('images');

        $this->addToAssertionCount(1);
    }

    protected function expectsEvent($eventClassName)
    {
        Event::listen($eventClassName, function ($event) use ($eventClassName) {
            $this->firedEvents[] = $eventClassName;
        });

        $this->beforeApplicationDestroyed(function () use ($eventClassName) {
            if (! in_array($eventClassName, $this->firedEvents)) {
                throw new Exception("Event {$eventClassName} not fired");
            }
        });
    }
}
