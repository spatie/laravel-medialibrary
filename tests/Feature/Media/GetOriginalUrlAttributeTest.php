<?php

test('the original url attribute exists', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertArrayHasKey('original_url', $media->toArray());
});

it('can get url of original image', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->original_url)->toEqual("/media/{$media->id}/test.jpg");
});
