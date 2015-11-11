<?php

namespace Spatie\MediaLibrary\Test\Events;

use Spatie\MediaLibrary\Events\CollectionHasBeenCleared;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Test\TestCase;

class EventTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_fire_the_media_added_event()
    {
        $this->expectsEvents([MediaHasBeenAdded::class]);

        $this->testModel->addMedia($this->getTestJpg())->toMediaLibrary();
    }

    /**
     * @test
     */
    public function it_will_fire_the_conversion_complete_event()
    {
        $this->expectsEvents([ConversionHasBeenCompleted::class]);

        $this->testModelWithConversion->addMedia($this->getTestJpg())->toCollection('images');
    }

    /**
     * @test
     */
    public function it_will_fire_the_collection_cleared_event()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaLibrary('images');

        $this->expectsEvents([CollectionHasBeenCleared::class]);

        $this->testModel->clearMediaCollection('images');
    }
}
