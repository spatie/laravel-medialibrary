<?php

it('can clean responsive images for deprecated conversions', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $deprecatedResponsiveImageFileName = 'test___deprecatedConversion_50_41.jpg';
    $deprecatedReponsiveImagesPath = $this->getMediaDirectory("1/responsive-images/{$deprecatedResponsiveImageFileName}");
    touch($deprecatedReponsiveImagesPath);

    $originalResponsiveImagesContent = $media->responsive_images;
    $newResponsiveImages = $originalResponsiveImagesContent;
    $newResponsiveImages['deprecatedConversion'] = $originalResponsiveImagesContent['thumb'];
    $newResponsiveImages['deprecatedConversion']['urls'][0] = $deprecatedResponsiveImageFileName;
    $media->responsive_images = $newResponsiveImages;
    $media->save();

    $this->artisan('media-library:clean');

    $media->refresh();

    expect($media->responsive_images)->toEqual($originalResponsiveImagesContent);
    $this->assertFileDoesNotExist($deprecatedReponsiveImagesPath);
});

it('can clean responsive images for active conversions without responsive images', function () {
    $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
    
    $thumbResponsiveImageFileName = "{$media->file_name}___thumb_340_280.jpg";
    $thumbReponsiveImagesPath = $this->getMediaDirectory("{$media->id}/responsive-images/{$thumbResponsiveImageFileName}");
    mkdir($this->getMediaDirectory("{$media->id}/responsive-images"));
    touch($thumbReponsiveImagesPath);

    $originalResponsiveImagesContent = $media->responsive_images;
    $newResponsiveImages = $originalResponsiveImagesContent;
    $newResponsiveImages['thumb']['base64svg'] = "data:image/svg+xml;base64,PCPg==";
    $newResponsiveImages['thumb']['urls'][0] = $thumbResponsiveImageFileName;
    $media->responsive_images = $newResponsiveImages;
    $media->save();

    $this->artisan('media-library:clean');

    $media->refresh();

    expect($media->responsive_images)->toEqual($originalResponsiveImagesContent);
    $this->assertFileDoesNotExist($thumbReponsiveImagesPath);
});