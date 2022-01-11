<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

test('the original url attribute exists', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertArrayHasKey('original_url', $media->toArray());
});

it('can get url of original image', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertEquals("/media/{$media->id}/test.jpg", $media->original_url);
});
