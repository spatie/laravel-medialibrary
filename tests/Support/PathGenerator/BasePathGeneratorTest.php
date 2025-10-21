<?php

use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\CustomPathGenerator;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\WrongCustomPathGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMorphMap;

beforeEach(function () {
    $this->config = app('config');

    $this->urlGenerator = new DefaultUrlGenerator($this->config);

    $this->pathGenerator = new CustomPathGenerator;

    $this->urlGenerator->setPathGenerator($this->pathGenerator);
});

it('can get the custom path for media without conversions', function () {
    $media = $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

    $this->urlGenerator->setMedia($media);

    $pathRelativeToRoot = md5($media->id).'/'.$media->file_name;

    expect($this->urlGenerator->getPathRelativeToRoot())->toEqual($pathRelativeToRoot);
});

it('can get the custom path for media with conversions', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();
    $conversion = ConversionCollection::createForMedia($media)->getByName('thumb');

    $this->urlGenerator
        ->setMedia($media)
        ->setConversion($conversion);

    $pathRelativeToRoot = md5($media->id).'/c/test-'.$conversion->getName().'.'.$conversion->getResultExtension($media->extension);

    expect($this->urlGenerator->getPathRelativeToRoot())->toEqual($pathRelativeToRoot);
});

it('can use a custom path generator on the model', function () {
    config()->set('media-library.custom_path_generators', [
        TestModelWithConversion::class => CustomPathGenerator::class,
    ]);

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
});

it('can use a custom path generator on a morph map model', function () {
    config()->set('media-library.custom_path_generators', [
        'test-model-with-morph-map' => CustomPathGenerator::class,
    ]);

    $media = $this->testModelWithMorphMap
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
});

it('can use a custom path generator on a morph map model via class', function () {
    config()->set('media-library.custom_path_generators', [
        TestModelWithMorphMap::class => CustomPathGenerator::class,
    ]);

    $media = $this->testModelWithMorphMap
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
});

it('can use a custom path generator on a morph map model inside of model', function () {
    $media = $this->testModelWithMorphMapInSideModel
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
});

it('get error when pass NotExistClass class', function () {
    PathGeneratorFactory::setCustomPathGenerators($this->testModelWithMorphMapInSideModel::class, NotExistClass::class);

    $media = $this->testModelWithMorphMapInSideModel
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
})
    ->throws(InvalidPathGenerator::class, sprintf('Path generator class `%s` doesn\'t exist', NotExistClass::class));

it('get error when pass WrongCustomPathGenerator class', function () {
    PathGeneratorFactory::setCustomPathGenerators($this->testModelWithMorphMapInSideModel::class, WrongCustomPathGenerator::class);

    $media = $this->testModelWithMorphMapInSideModel
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection();

    expect($media->getUrl())->toEqual('/media/c4ca4238a0b923820dcc509a6f75849b/test.jpg');
})
    ->throws(InvalidPathGenerator::class, sprintf('Path generator class `%s` must implement `%s`', WrongCustomPathGenerator::class, PathGenerator::class));
