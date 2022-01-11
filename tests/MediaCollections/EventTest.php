<?php

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Tests\TestCase;


beforeEach(function () {
    parent::setup();

    Event::fake();
});

it('will fire the media added event', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    Event::assertDispatched(MediaHasBeenAdded::class);
});
