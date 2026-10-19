<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;

it('can get an url of an original item', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect("/media/{$media->id}/test.jpg")->toEqual($media->getUrl());
});

it('can get an url of a derived image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    expect($media->getUrl($conversionName))->toEqual("/media/{$media->id}/conversions/test-{$conversionName}.jpg");
});

it('returns an exception when getting an url for an unknown conversion', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(InvalidConversion::class);

    $media->getUrl('unknownConversionName');
});

it('can get the full url of an original item', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect("http://localhost/media/{$media->id}/test.jpg")->toEqual($media->getFullUrl());
});

it('can get the full url of a derived image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    expect($media->getFullUrl($conversionName))->toEqual("http://localhost/media/{$media->id}/conversions/test-{$conversionName}.jpg");
});

it('throws an exception when trying to get a temporary url on local disk', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(RuntimeException::class);

    $media->getTemporaryUrl(Carbon::now()->addMinutes(5));
});

it('passes the raw path to the disk when generating a temporary url on local disk', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->usingFileName('tést.jpg')
        ->toMediaCollection();

    $disk = Mockery::mock(Storage::disk('public'))->makePartial();
    $disk->shouldReceive('temporaryUrl')
        ->once()
        ->withArgs(fn (string $path) => $path === "{$media->id}/tést.jpg")
        ->andReturn('https://example.com/temporary-url');

    Storage::set('public', $disk);

    expect($media->getTemporaryUrl(Carbon::now()->addMinutes(5)))
        ->toEqual('https://example.com/temporary-url');
});
