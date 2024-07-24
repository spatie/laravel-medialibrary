<?php

use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\MediaLibrary\Tests\TestSupport\TestMediaModel;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function () {
    foreach (range(1, 3) as $i) {
        $this
            ->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
    }
});

it('can return a stream of media', function () {
    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

    expect($zipStreamResponse->getMediaItems()->count())->toEqual(count(Media::all()));

    Route::get('stream-test', fn () => $zipStreamResponse);

    $response = $this->get('stream-test');

    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);
});

it('can return a stream of multiple files with the same filename', function () {
    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test.jpg');
    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test (1).jpg');
    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test (2).jpg');
});

it('will respect the filename set by getDownloadFilename method', function () {
    $zipStreamResponse = MediaStream::create('my-media.zip')
        ->addMedia(Media::find(1))
        ->addMedia(TestMediaModel::find(2))
        ->addMedia(TestMediaModel::find(2));

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test.jpg');
    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'overriden_testing.jpg');
    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'overriden_testing (1).jpg');
});

test('media can be added to it one by one', function () {
    $zipStreamResponse = MediaStream::create('my-media.zip')
        ->addMedia(Media::find(1))
        ->addMedia(Media::find(2));

    expect($zipStreamResponse->getMediaItems()->count())->toEqual(2);
});

test('an array of media can be added to it', function () {
    $zipStreamResponse = MediaStream::create('my-media.zip')
        ->addMedia([Media::find(1), Media::find(2)]);

    expect($zipStreamResponse->getMediaItems()->count())->toEqual(2);
});

test('media with zip file folder prefix property saved in correct zip folder', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties([
            'zip_filename_prefix' => 'folder/subfolder/',
        ])
        ->toMediaCollection();

    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (2).jpg');

    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test.jpg');
});

test('media with zip file folder prefix property saved in correct zip folder and correct suffix', function () {
    foreach (range(1, 2) as $i) {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
    }

    foreach (range(1, 2) as $i) {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties([
                'zip_filename_prefix' => 'folder/subfolder/',
            ])
            ->toMediaCollection();
    }

    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test.jpg');
    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (1).jpg');
    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (2).jpg');

    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test.jpg');
    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test (1).jpg');
});

test('media with zip file prefix property saved with correct prefix', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties([
            'zip_filename_prefix' => 'just_a_string_prefix ',
        ])
        ->toMediaCollection();

    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();
    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'just_a_string_prefix test.jpg');
});
