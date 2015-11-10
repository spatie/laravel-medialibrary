<?php
namespace Spatie\MediaLibrary\Test\Events;

use Spatie\MediaLibrary\Events\CollectionClearedEvent;
use Spatie\MediaLibrary\Events\ConversionCompleteEvent;
use Spatie\MediaLibrary\Events\MediaAddedEvent;
use Spatie\MediaLibrary\Test\TestCase;

class EventTest extends TestCase
{

    /**
     * @test
     */
    public function it_will_fire_the_made_has_been_stored_event()
    {
        $this->expectsEvents([MediaAddedEvent::class]);

        $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();
    }

    /**
     * @test
     */
    public function it_will_fire_the_conversion_has_finished_event()
    {
        $this->expectsEvents([ConversionCompleteEvent::class]);

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

        $this->expectsEvents([CollectionClearedEvent::class]);

        $this->testModel->clearMediaCollection('images');
    }
}
