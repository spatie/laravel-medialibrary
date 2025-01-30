<?php

use Programic\MediaLibrary\Conversions\ConversionCollection;
use Programic\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Programic\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

beforeEach(function () {
    $this->config = app('config');

    $this->media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

    $this->conversion = ConversionCollection::createForMedia($this->media)->getByName('thumb');

    $this->conversionKeepingOriginalImageFormat = ConversionCollection::createForMedia($this->media)->getByName('keep_original_format');

    $this->urlGenerator = new DefaultUrlGenerator($this->config);
    $this->pathGenerator = new DefaultPathGenerator;

    $this->urlGenerator
        ->setMedia($this->media)
        ->setConversion($this->conversion)
        ->setPathGenerator($this->pathGenerator);
});

it('can get the path relative to the root of media folder', function () {
    $pathRelativeToRoot = $this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

    expect($this->urlGenerator->getPathRelativeToRoot())->toEqual($pathRelativeToRoot);
});

it('can get the path relative to the root of media folder when keeping the original image format', function () {
    $this->urlGenerator->setConversion($this->conversionKeepingOriginalImageFormat);

    $pathRelativeToRoot = $this->media->id
        .'/conversions/'.
        'test-'.$this->conversionKeepingOriginalImageFormat->getName()
        .'.png';

    expect($this->urlGenerator->getPathRelativeToRoot())->toEqual($pathRelativeToRoot);
});

it('appends a version string when versioning is enabled', function () {
    config()->set('media-library.version_urls', true);

    $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg?v='.$this->media->updated_at->timestamp;

    expect($this->urlGenerator->getUrl())->toEqual($url);

    config()->set('media-library.version_urls', false);

    $url = '/media/'.$this->media->id.'/conversions/test-'.$this->conversion->getName().'.jpg';

    expect($this->urlGenerator->getUrl())->toEqual($url);
});

it('can get the responsive images directory url', function () {
    $this->config->set('filesystems.disks.public.url', 'http://localhost/media/');

    expect($this->urlGenerator->getResponsiveImagesDirectoryUrl())->toEqual('/media/1/responsive-images/');
});
