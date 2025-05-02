<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\TemporaryDirectory\TemporaryDirectory;

function getAvailableContent(Media $media): string
{
    $temporaryDirectory = (new TemporaryDirectory)->create();
    ob_start();
    @$media->toAvailableInlineResponse(request(), ['small', 'medium', 'large'])
        ->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    file_put_contents(
        $tmpFile = $temporaryDirectory->path('response.xxx'),
        $content
    );
    return $tmpFile;
}

it('sends the content of first available conversion', function () {
    $media = $this->testModelWithMultipleConversions->addMedia(
        $testJpeg = $this->getTestJpg()
    )->preservingOriginal()->toMediaCollection();

    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionNotGenerated('large');

    $tmpFile = getAvailableContent($media);
    $this->assertFileEquals($testJpeg, $tmpFile);

    $media->markAsConversionGenerated('large');
    $tmpFile = getAvailableContent($media);
    $expectedFile = $media->getPath('large');
    $this->assertFileEquals($expectedFile, $tmpFile);

    $media->markAsConversionGenerated('medium');
    $tmpFile = getAvailableContent($media);
    $expectedFile = $media->getPath('medium');
    $this->assertFileEquals($expectedFile, $tmpFile);

    $media->markAsConversionGenerated('small');
    $tmpFile = getAvailableContent($media);
    $expectedFile = $media->getPath('small');
    $this->assertFileEquals($expectedFile, $tmpFile);
});
