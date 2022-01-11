<?php

use Carbon\Carbon;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can get an url of an original item', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertEquals($media->getUrl(), "/media/{$media->id}/test.jpg");
});

it('can get an url of a derived image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $this->assertEquals("/media/{$media->id}/conversions/test-{$conversionName}.jpg", $media->getUrl($conversionName));
});

it('returns an exception when getting an url for an unknown conversion', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(InvalidConversion::class);

    $media->getUrl('unknownConversionName');
});

it('can get the full url of an original item', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertEquals($media->getFullUrl(), "http://localhost/media/{$media->id}/test.jpg");
});

it('can get the full url of a derived image', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionName = 'thumb';

    $this->assertEquals("http://localhost/media/{$media->id}/conversions/test-{$conversionName}.jpg", $media->getFullUrl($conversionName));
});

it('throws an exception when trying to get a temporary url on local disk', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    $this->expectException(RuntimeException::class);

    $media->getTemporaryUrl(Carbon::now()->addMinutes(5));
});
