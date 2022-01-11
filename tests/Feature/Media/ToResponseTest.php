<?php

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

uses(TestCase::class);

test('to response sends the content', function () {
    $media = $this->testModel->addMedia($testPdf = $this->getTestPdf())->preservingOriginal()->toMediaCollection();

    ob_start();
    @$media->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory())->create();
    file_put_contents($temporaryDirectory->path('response.pdf'), $content);

    $this->assertFileEquals($testPdf, $temporaryDirectory->path('response.pdf'));
});

test('to response sends correct attachment header', function () {
    $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

    $response = $media->toResponse(request());

    $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
});

test('to inline response sends correct attachment header', function () {
    $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

    $response = $media->toInlineResponse(request());

    $this->assertEquals('inline; filename="test.pdf"', $response->headers->get('Content-Disposition'));
});
