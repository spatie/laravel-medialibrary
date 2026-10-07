<?php

use Spatie\MediaLibrary\Conversions\Actions\PerformManipulationsAction;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

beforeEach(function () {
    $this->conversionName = 'test';
    $this->conversion = new Conversion($this->conversionName);
});

it('does not perform manipulations if not necessary', function () {
    $imageFile = $this->getTestJpg();
    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionTempFile = (new PerformManipulationsAction)->execute(
        $media,
        $this->conversion->withoutManipulations(),
        $imageFile
    );

    expect($conversionTempFile)->toEqual($imageFile);
});

it('deletes temporary directory if copied file is empty', function () {
    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionCollection = new ConversionCollection([
        $this->conversion,
    ]);

    $tempDirectoryPath = null;
    $this->mock(Filesystem::class, function ($mock) use (&$tempDirectoryPath) {
        $mock->shouldReceive('copyFromMediaLibrary')->andReturnUsing(function ($media, $path) use (&$tempDirectoryPath) {
            $tempDirectoryPath = dirname($path);
            touch($path);

            return $path;
        });
    });

    app(FileManipulator::class)->performConversions($conversionCollection, $media);

    expect(file_exists($tempDirectoryPath))->toBeFalse();
});

it('cleans up temporary directory when an exception is thrown during conversions', function () {
    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionCollection = new ConversionCollection([
        $this->conversion,
    ]);

    $tempDirectoryPath = null;
    $this->mock(Filesystem::class, function ($mock) use (&$tempDirectoryPath) {
        $mock->shouldReceive('copyFromMediaLibrary')->andReturnUsing(function ($media, $path) use (&$tempDirectoryPath) {
            $tempDirectoryPath = dirname($path);
            file_put_contents($path, 'dummy content');
            throw new Exception('Conversion failed');
        });
    });

    try {
        app(FileManipulator::class)->performConversions($conversionCollection, $media);
    } catch (Exception $e) {
        // Expected exception
    }

    expect(file_exists($tempDirectoryPath))->toBeFalse();
});
