<?php

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

beforeEach(function () {
    $this->model = (new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this
                ->addMediaConversion('preview')
                ->fit(Fit::Crop, 50, 50)
                ->performOnCollections('avatar')
                ->format('png')
                ->nonQueued();

            $this
                ->addMediaConversion('preview')
                ->fit(Fit::Crop, 300, 100)
                ->performOnCollections('signature')
                ->format('jpeg')
                ->nonQueued();

            $this
                ->addMediaConversion('web')
                ->format('webp')
                ->nonQueued();
        }

        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('avatar')->acceptsMimeTypes(['image/png'])->singleFile();
            $this->addMediaCollection('signature')->acceptsMimeTypes(['image/jpeg'])->singleFile();
        }
    })::create(['name' => 'testmodel']);

    $avatarMedia = $this->model
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->toMediaCollection('avatar');
    $avatarMedia->save();

    $signatureMedia = $this->model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('signature');
    $signatureMedia->save();

    $this->avatarMedia = $avatarMedia->refresh();
    $this->signatureMedia = $signatureMedia->refresh();
});

it('will apply correct conversions for media in different collections', function () {
    $conversionCollection = ConversionCollection::createForMedia($this->avatarMedia);
    /** @var ConversionCollection<int, Conversion> $conversions */
    $conversions = $conversionCollection->getConversions('avatar');

    expect($conversions->count())->toBe(2)
        ->and($conversions->first()->getName())->toBe('preview')
        ->and($conversions->first()->getResultExtension())->toBe('png')
        ->and($conversions->first()->getManipulations()->toArray())->toMatchArray([
            'fit' => [Fit::Crop, 50, 50],
            'format' => ['png'],
        ])
        ->and($conversions->last()->getName())->toBe('web')
        ->and($conversions->last()->getResultExtension())->toBe('webp')
        ->and($conversions->last()->getManipulations()->toArray())->toMatchArray([
            'format' => ['webp'],
        ]);

    $conversionCollection = ConversionCollection::createForMedia($this->signatureMedia);
    /** @var ConversionCollection<int, Conversion> $conversions */
    $conversions = $conversionCollection->getConversions('signature');

    expect($conversions->count())->toBe(2)
        ->and($conversions->first()->getName())->toBe('preview')
        ->and($conversions->first()->getResultExtension())->toBe('jpeg')
        ->and($conversions->first()->getManipulations()->toArray())->toMatchArray([
            'fit' => [Fit::Crop, 300, 100],
            'format' => ['jpeg'],
        ])
        ->and($conversions->last()->getName())->toBe('web')
        ->and($conversions->last()->getResultExtension())->toBe('webp')
        ->and($conversions->last()->getManipulations()->toArray())->toMatchArray([
            'format' => ['webp'],
        ]);
});

it('will generate correct filenames for media in different collections but with identically named conversions', function () {
    expect($this->model->getFirstMediaUrl('avatar', 'preview'))->toEndWith('.png');
    expect($this->model->getFirstMediaUrl('avatar', 'web'))->toEndWith('.webp');

    expect($this->model->getFirstMediaUrl('signature', 'preview'))->toEndWith('.jpeg');
    expect($this->model->getFirstMediaUrl('signature', 'web'))->toEndWith('.webp');
});
