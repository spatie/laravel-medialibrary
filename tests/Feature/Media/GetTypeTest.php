<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can return the file mime', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertEquals('image/jpeg', $media->mime_type);
});
