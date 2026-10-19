<?php

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\Conversions\Actions\PerformManipulationsAction;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\Events\ConversionWillStartEvent;
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

it('deletes temporary directory if copied file is empty or missing', function (bool $createFile) {
    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionCollection = new ConversionCollection([
        $this->conversion,
    ]);

    $tempDirectoryPath = null;
    $this->mock(Filesystem::class, function ($mock) use (&$tempDirectoryPath, $createFile) {
        $mock->shouldReceive('copyFromMediaLibrary')->andReturnUsing(function ($media, $path) use (&$tempDirectoryPath, $createFile) {
            $tempDirectoryPath = dirname($path);

            if ($createFile) {
                touch($path);
            }

            return $path;
        });
    });

    $fileManipulator = app(FileManipulator::class);

    expect($fileManipulator->performConversions($conversionCollection, $media))->toBe($fileManipulator)
        ->and($tempDirectoryPath)->toBeString()->not->toBeDirectory()
        ->and($media->getPath())->toBeFile();
})->with([true, false]);

it('cleans up temporary directory and propagates failures when copying or converting', function (bool $failDuringCopy) {
    $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

    $conversionCollection = new ConversionCollection([
        $this->conversion,
    ]);

    $tempDirectoryPath = null;
    $exception = new RuntimeException('Conversion failed');

    if (! $failDuringCopy) {
        Event::listen(ConversionWillStartEvent::class, function () use ($exception) {
            throw $exception;
        });
    }

    $this->mock(Filesystem::class, function ($mock) use (&$tempDirectoryPath, $failDuringCopy, $exception) {
        $mock->shouldReceive('copyFromMediaLibrary')->andReturnUsing(function ($media, $path) use (&$tempDirectoryPath, $failDuringCopy, $exception) {
            $tempDirectoryPath = dirname($path);
            copy($media->getPath(), $path);

            if ($failDuringCopy) {
                throw $exception;
            }

            return $path;
        });
    });

    expect(fn () => app(FileManipulator::class)->performConversions($conversionCollection, $media))
        ->toThrow($exception);

    expect($tempDirectoryPath)->toBeString()->not->toBeDirectory()
        ->and($media->getPath())->toBeFile();
})->with([true, false]);
