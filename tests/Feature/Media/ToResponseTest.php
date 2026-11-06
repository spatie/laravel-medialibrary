<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\TemporaryDirectory\TemporaryDirectory;

test('to response sends the content', function () {
    $media = $this->testModel->addMedia($testPdf = $this->getTestPdf())->preservingOriginal()->toMediaCollection();

    ob_start();
    @$media->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.pdf'), $content);

    $this->assertFileEquals($testPdf, $temporaryDirectory->path('response.pdf'));
});

test('to response sends correct attachment header', function () {
    $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

    $response = $media->toResponse(request());

    expect($response->headers->get('Content-Disposition'))->toEqual('attachment; filename="test.pdf"');
});

test('to inline response sends correct attachment header', function () {
    $media = $this->testModel->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

    $response = $media->toInlineResponse(request());

    expect($response->headers->get('Content-Disposition'))->toEqual('inline; filename="test.pdf"');
});

test('to response throws on non-existing conversions', function () {
    $media = $this->testModel
        ->addMedia($this->getTestPdf())
        ->preservingOriginal()
        ->toMediaCollection();

    expect(fn () => $media->toResponse(request(), 'non-existing-conversion'))
        ->toThrow(InvalidConversion::class, 'There is no conversion named `non-existing-conversion`');
});

test('conversion responses use the conversion mime type', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->toMediaCollection();

    expect($media->mime_type)->toBe('image/png');
    expect($media->toResponse(request(), 'thumb')->headers->get('Content-Type'))->toBe('image/jpeg');
    expect($media->toInlineResponse(request(), 'thumb')->headers->get('Content-Type'))->toBe('image/jpeg');
});

test('conversion responses read metadata from the conversions disk', function () {
    $media = $this->testModelWithConversionsOnOtherDisk
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->toMediaCollection('thumb');

    expect($media->disk)->not->toBe($media->conversions_disk);
    expect($media->toResponse(request(), 'thumb')->headers->get('Content-Type'))->toBe('image/jpeg');
});

test('conversion responses use a binary mime type when metadata is unavailable', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->toMediaCollection();

    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('size')->once()->andReturn(100);
    $disk->shouldReceive('mimeType')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with($media->conversions_disk)->once()->andReturn($disk);

    expect($media->toResponse(request(), 'thumb')->headers->get('Content-Type'))->toBe('application/octet-stream');
});
