<?php

use Illuminate\Support\Facades\Event;
use Programic\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

it('will fire the media added event', function () {
    Event::fake();

    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    Event::assertDispatched(MediaHasBeenAddedEvent::class);
});
