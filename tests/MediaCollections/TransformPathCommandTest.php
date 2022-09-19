<?php

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestPathGenerator;
use Spatie\MediaLibrary\Tests\TestSupport\TestUuidPathGenerator;

function getSourceExpectedPath(Media $media, ?string $conversionName = null): string
{
    return $media->model->id
        . '/' . md5($media->id)
        . ($conversionName
            ? '/custom_conversions/test-' . $conversionName . '.jpg'
            : '/test.jpg');
}

function getTargetExpectedPath(Media $media, ?string $conversionName = null): string
{
    return $media->uuid
        . ($conversionName
            ? '/custom_conversions/test-' . $conversionName . '.jpg'
            : '/test.jpg');
}

beforeEach(function () {
    config([
        'media-library.path_generator' => TestPathGenerator::class,
    ]);

    $this->media['model1']['collection1'] = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection1');

    $this->media['model1']['collection2'] = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection2');

    $this->media['model2']['collection1'] = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection1');

    $this->media['model2']['collection2'] = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection2');

    /**
     * @var Media $mediaItem
     */
    foreach (Arr::flatten($this->media) as $mediaItem) {
        config([
            'media-library.path_generator' => TestPathGenerator::class,
        ]);

        expect($this->getMediaDirectory(getSourceExpectedPath($mediaItem)))->toBeFile();

        foreach ($mediaItem->getGeneratedConversions() as $conversionName => $conversionPresent) {
            $oldExpectedConversionPath =
                $mediaItem->model->id
                . '/' . md5($mediaItem->id)
                . '/custom_conversions/test-' . $conversionName . '.jpg';
            expect($this->getMediaDirectory($oldExpectedConversionPath))->toBeFile();
        }
    }
});


it('can transform file paths', function () {
    $media = $this->media['model1']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
    ]);

    // Ensure files are gone from 'old' location
    expect($this->getMediaDirectory(getSourceExpectedPath($media)))
        ->not()
        ->toBeFile();

    // Ensure files have arrived at the 'new' location
    expect($this->getMediaDirectory(getTargetExpectedPath($media)))
        ->toBeFile();
});

it('can transform file paths including conversions', function () {
    $media = $this->media['model2']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
    ]);

    // Ensure files are gone from 'old' location
    expect($this->getMediaDirectory(getSourceExpectedPath($media)))
        ->not()
        ->toBeFile();

    foreach ($media->getMediaConversionNames() as $conversionName) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media, $conversionName)))
            ->not()
            ->toBeFile();
    }

    // Ensure files have arrived at the 'new' location
    expect($this->getMediaDirectory(getTargetExpectedPath($media)))
        ->toBeFile();

    foreach ($media->getMediaConversionNames() as $conversionName) {
        expect($this->getMediaDirectory(getTargetExpectedPath($media, $conversionName)))
            ->toBeFile();
    }
});

it('can transform file paths from a specific model type', function () {
    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        'modelType' => 'Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel',
    ]);

    // Ensure files are gone from 'old' location for model1
    foreach (Arr::flatten($this->media['model1']) as $media) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media)))
            ->not()
            ->toBeFile();
    }

    // Ensure files are still in place from 'old' location for model2, including
    // the conversions
    foreach (Arr::flatten($this->media['model2']) as $media) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media)))
            ->toBeFile();

        foreach ($media->getMediaConversionNames() as $conversionName) {
            expect($this->getMediaDirectory(getSourceExpectedPath($media, $conversionName)))
                ->toBeFile();
        }
    }

    // Ensure files have arrived in new location for model1
    foreach (Arr::flatten($this->media['model1']) as $media) {
        expect($this->getMediaDirectory(getTargetExpectedPath($media)))
            ->toBeFile();
    }
});

it('can transform file paths from a specific collection', function () {
    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        'collectionName' => 'collection2',
    ]);

    // Ensure files are gone from 'old' location for collection2, including
    // any conversions
    foreach (Arr::pluck($this->media, 'collection2') as $media) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media)))
            ->not()
            ->toBeFile();

        foreach ($media->getMediaConversionNames() as $conversionName) {
            expect($this->getMediaDirectory(getSourceExpectedPath($media, $conversionName)))
                ->not()
                ->toBeFile();
        }
    }

    // Ensure files are still in place from 'old' location for collection1, including
    // the conversions
    foreach (Arr::pluck($this->media, 'collection1') as $media) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media)))
            ->toBeFile();

        foreach ($media->getMediaConversionNames() as $conversionName) {
            expect($this->getMediaDirectory(getSourceExpectedPath($media, $conversionName)))
                ->toBeFile();
        }
    }

    // Ensure files have arrived in new location for collection2, including any
    // conversions
    foreach (Arr::pluck($this->media, 'collection2') as $media) {
        expect($this->getMediaDirectory(getTargetExpectedPath($media)))
            ->toBeFile();

        foreach ($media->getMediaConversionNames() as $conversionName) {
            expect($this->getMediaDirectory(getTargetExpectedPath($media, $conversionName)))
                ->toBeFile();
        }
    }
});

it('can transform file paths from a specific model type and collection', function () {
    $moved = $this->media['model2']['collection2'];
    $notMoving = Arr::except(Arr::dot($this->media), 'model2.collection2');

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        'modelType' => 'Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion',
        'collectionName' => 'collection2',
    ]);

    // Ensure files are still in place for all the models that ought not to have
    // moved, including any conversions.  (Everything except model2/collection2)
    foreach ($notMoving as $media) {
        expect($this->getMediaDirectory(getSourceExpectedPath($media)))
            ->toBeFile();

        foreach ($media->getMediaConversionNames() as $conversionName) {
            expect($this->getMediaDirectory(getSourceExpectedPath($media, $conversionName)))
                ->toBeFile();
        }
    }

    // Ensure model2/collection1 moved
    expect($this->getMediaDirectory(getTargetExpectedPath($moved)))
        ->toBeFile();

    foreach ($moved->getMediaConversionNames() as $conversionName) {
        expect($this->getMediaDirectory(getTargetExpectedPath($moved, $conversionName)))
            ->toBeFile();
    }
});

it('can transform paths where conversions are on another disk', function () {
    $media = $this->testModelWithConversionsOnOtherDisk
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('thumb');

    // Ensure files are in place from new model addition above
    expect($this->getMediaDirectory(getSourceExpectedPath($media)))
        ->toBeFile();

    foreach ($media->getMediaConversionNames() as $conversionName) {
        expect($this->getTempDirectory() . '/media2/' . getSourceExpectedPath($media, $conversionName))
            ->toBeFile();
    }

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        'modelType' => 'Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionsOnOtherDisk',
    ]);

    // Ensure files have disappeared from old location
    expect($this->getMediaDirectory(getSourceExpectedPath($media)))
        ->not()
        ->toBeFile();

    foreach ($media->getMediaConversionNames() as $conversionName) {
        expect($this->getTempDirectory() . '/media2/' . getSourceExpectedPath($media, $conversionName))
            ->not()
            ->toBeFile();
    }

    // Ensure files are in new location
    expect($this->getMediaDirectory(getTargetExpectedPath($media)))
        ->toBeFile();

    foreach ($media->getMediaConversionNames() as $conversionName) {
        expect($this->getTempDirectory() . '/media2/' . getTargetExpectedPath($media, $conversionName))
            ->toBeFile();
    }
});

it('will error if source files are missing', function () {
    $media = $this->media['model1']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    unlink($this->getMediaDirectory(getSourceExpectedPath($media)));

    $this->artisan('media-library:transform-path', [
            'sourceGeneratorClass' => TestPathGenerator::class,
            'targetGeneratorClass' => TestUuidPathGenerator::class,
        ])
        ->assertExitCode(1)
        ->expectsOutput(
            '#1 (test #1 public/public): Source media is missing (1/c4ca4238a0b923820dcc509a6f75849b/test.jpg)'
        );
});

it('will not error if source files are missing but check is disabled', function () {
    $media = $this->media['model1']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    unlink($this->getMediaDirectory(getSourceExpectedPath($media)));

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        '--ignore-missing-source-files' => true
    ])->assertExitCode(0);
});

it('will error if target files already exist', function () {
    $media = $this->media['model1']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    mkdir($this->getMediaDirectory() . '/' . $media->uuid);
    touch($this->getMediaDirectory(getTargetExpectedPath($media)));

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
    ])
        ->assertExitCode(1)
        ->expectsOutput('#1 (test #1 public/public): Target media already exists (' . $media->uuid . '/test.jpg)');
});

it('will not error if target files already exist but check has been disabled', function () {
    $media = $this->media['model1']['collection1'];
    Media::where('id', '<>', $media->id)->delete();

    mkdir($this->getMediaDirectory() . '/' . $media->uuid);
    touch($this->getMediaDirectory(getTargetExpectedPath($media)));

    $this->artisan('media-library:transform-path', [
        'sourceGeneratorClass' => TestPathGenerator::class,
        'targetGeneratorClass' => TestUuidPathGenerator::class,
        '--ignore-existing-target-files' => true,
    ])->assertExitCode(0);

    // Touched (0 length) file should now be replaced with real image
    $testJpgFileSize = filesize($this->getTestFilesDirectory('test.jpg'));
    expect($this->getMediaDirectory(getTargetExpectedPath($media)))
        ->toBeFile();
    expect(filesize($this->getMediaDirectory(getTargetExpectedPath($media))))
        ->toEqual($testJpgFileSize);
});
