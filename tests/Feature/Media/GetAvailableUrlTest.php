<?php

it('can get a url of first available conversion', function () {
    $media = $this->testModelWithMultipleConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionGenerated('large');

    expect($media->getAvailableUrl(['small', 'medium', 'large']))->toEqual("/media/{$media->id}/conversions/test-large.jpg");
    expect($media->getAvailableFullUrl(['small', 'medium', 'large']))->toEqual("http://localhost/media/{$media->id}/conversions/test-large.jpg");
    expect($media->getAvailablePath(['small', 'medium', 'large']))->toEqual($this->makePathOsSafe($this->getMediaDirectory()."/{$media->id}/conversions/test-large.jpg"));
    expect($media->getAvailablePathRelativeToRoot(['small', 'medium', 'large']))->toEqual("{$media->id}/conversions/test-large.jpg");

    $media->markAsConversionGenerated('medium');

    expect($media->getAvailableUrl(['small', 'medium', 'large']))->toEqual("/media/{$media->id}/conversions/test-medium.jpg");
    expect($media->getAvailableFullUrl(['small', 'medium', 'large']))->toEqual("http://localhost/media/{$media->id}/conversions/test-medium.jpg");
    expect($media->getAvailablePath(['small', 'medium', 'large']))->toEqual($this->makePathOsSafe($this->getMediaDirectory()."/{$media->id}/conversions/test-medium.jpg"));
    expect($media->getAvailablePathRelativeToRoot(['small', 'medium', 'large']))->toEqual("{$media->id}/conversions/test-medium.jpg");

    $media->markAsConversionGenerated('small');

    expect($media->getAvailableUrl(['small', 'medium', 'large']))->toEqual("/media/{$media->id}/conversions/test-small.jpg");
    expect($media->getAvailableFullUrl(['small', 'medium', 'large']))->toEqual("http://localhost/media/{$media->id}/conversions/test-small.jpg");
    expect($media->getAvailablePath(['small', 'medium', 'large']))->toEqual($this->makePathOsSafe($this->getMediaDirectory()."/{$media->id}/conversions/test-small.jpg"));
    expect($media->getAvailablePathRelativeToRoot(['small', 'medium', 'large']))->toEqual("{$media->id}/conversions/test-small.jpg");
});

it('uses original url if no conversion has been generated yet', function () {
    $media = $this->testModelWithMultipleConversions->addMedia($this->getTestJpg())->toMediaCollection();
    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionNotGenerated('large');

    expect($media->getAvailableUrl(['small', 'medium', 'large']))->toEqual("/media/{$media->id}/test.jpg");
    expect($media->getAvailableFullUrl(['small', 'medium', 'large']))->toEqual("http://localhost/media/{$media->id}/test.jpg");
    expect($media->getAvailablePath(['small', 'medium', 'large']))->toEqual($this->makePathOsSafe($this->getMediaDirectory()."/{$media->id}/test.jpg"));
    expect($media->getAvailablePathRelativeToRoot(['small', 'medium', 'large']))->toEqual("{$media->id}/test.jpg");
});
