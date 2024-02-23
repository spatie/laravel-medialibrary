<?php

use Spatie\MediaLibrary\Support\ImageFactory;

it('can correctly convert an image with orientation exif data', function () {
    $image = ImageFactory::load($this->getTestImageWithOrientation());
    $exif = exif_read_data($this->getTestImageWithOrientation());

    // Check if image is shot in landscape
    $isLandscape = $image->getWidth() >= $image->getHeight();

    // Check if exif data is present & if the image needs to set from landscape to portrait or the other way around
    if (in_array($exif['Orientation'], ['6', '8'])) {
        $isLandscape = !$isLandscape;
    }

    // Do conversion
    $media = $this->testModelWithPreviewConversion->addMedia($this->getTestImageWithOrientation())->toMediaCollection();

    // Get conversion
    $conversion = ImageFactory::load($media->getPath('preview'));

    // Check if conversion is landscape
    $conversionIsLandscape = $conversion->getWidth() >= $conversion->getHeight();

    expect($isLandscape)->toEqual($conversionIsLandscape);
});
