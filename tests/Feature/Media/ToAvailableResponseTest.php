<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\TemporaryDirectory\TemporaryDirectory;

function getAvailableContent(Media $media): string
{
    ob_start();
    @$media->toAvailableInlineResponse(request(), ['small', 'medium', 'large'])->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    $tmpFile = $temporaryDirectory->path('response.xxx');
    file_put_contents($tmpFile, $content);

    return $tmpFile;
}

it('sends the content of first available conversion', function () {
    $media = $this->testModelWithMultipleConversions
        ->addMedia($testJpeg = $this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionNotGenerated('large');

    $tmpFile = getAvailableContent($media);
    $this->assertFileEquals($testJpeg, $tmpFile);

    $media->markAsConversionGenerated('large');
    $tmpFile = getAvailableContent($media);
    $this->assertFileEquals($media->getPath('large'), $tmpFile);

    $media->markAsConversionGenerated('medium');
    $tmpFile = getAvailableContent($media);
    $this->assertFileEquals($media->getPath('medium'), $tmpFile);

    $media->markAsConversionGenerated('small');
    $tmpFile = getAvailableContent($media);
    $this->assertFileEquals($media->getPath('small'), $tmpFile);
});
