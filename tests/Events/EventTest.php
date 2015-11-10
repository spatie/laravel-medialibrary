<?php
namespace Spatie\MediaLibrary\Test\Events;

use Spatie\MediaLibrary\Events\CollectionHasBeenClearedEvent;
use Spatie\MediaLibrary\Events\ConversionHasFinishedEvent;
use Spatie\MediaLibrary\Events\MediaHasBeenStoredEvent;
use Spatie\MediaLibrary\Test\TestCase;

class EventTest extends TestCase
{

    /**
     * @test
     */
    public function it_will_fire_the_made_has_been_stored_event()
    {
        $this->expectsEvents([MediaHasBeenStoredEvent::class]);

        $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();
    }

    /**
     * @test
     */
    public function it_will_fire_the_conversion_has_finished_event()
    {
        $this->expectsEvents([ConversionHasFinishedEvent::class]);

        $this->testModelWithConversion->addMedia($this->getTestJpg())->toCollection('images');
    }

    /**
     * @test
     */
    public function it_will_fire_the_collection_has_been_cleared_event()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaLibrary('images');

        $this->expectsEvents([CollectionHasBeenClearedEvent::class]);

        $this->testModel->clearMediaCollection('images');
    }
}
