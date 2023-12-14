<?php

test('the preview url attribute exists', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertArrayHasKey('preview_url', $media->toArray());
});

it('can get url of preview image', function () {
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'preview';

    expect($media->preview_url)->toEqual("/media/{$media->id}/conversions/test-{$conversionName}.jpg");
});
