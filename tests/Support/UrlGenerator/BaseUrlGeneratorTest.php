<?php

use Illuminate\Config\Repository;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->config = app('config');

    $this->media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    $this->conversion = ConversionCollection::createForMedia($this->media)->getByName('thumb');

    $this->conversionKeepingOriginalImageFormat = ConversionCollection::createForMedia($this->media)->getByName('keep_original_format');

    $this->urlGenerator = new DefaultUrlGenerator($this->config);
    $this->pathGenerator = new DefaultPathGenerator();

    $this->urlGenerator
        ->setMedia($this->media)
        ->setConversion($this->conversion)
        ->setPathGenerator($this->pathGenerator);
});

it('can get the path relative to the root of media folder', function () {
    $pathRelativeToRoot = $this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

    $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
});

it('can get the path relative to the root of media folder when keeping the original image format', function () {
    $this->urlGenerator->setConversion($this->conversionKeepingOriginalImageFormat);

    $pathRelativeToRoot = $this->media->id
        .'/conversions/'.
        'test-'.$this->conversionKeepingOriginalImageFormat->getName()
        .'.png';

    $this->assertEquals($pathRelativeToRoot, $this->urlGenerator->getPathRelativeToRoot());
});

it('appends a version string when versioning is enabled', function () {
    config()->set('media-library.version_urls', true);

    $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg?v='.$this->media->updated_at->timestamp;

    $this->assertEquals($url, $this->urlGenerator->getUrl());

    config()->set('media-library.version_urls', false);

    $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

    $this->assertEquals($url, $this->urlGenerator->getUrl());
});

it('can get the responsive images directory url', function () {
    $this->config->set('filesystems.disks.public.url', 'http://localhost/media/');

    $this->assertEquals('http://localhost/media/1/responsive-images/', $this->urlGenerator->getResponsiveImagesDirectoryUrl());
});
