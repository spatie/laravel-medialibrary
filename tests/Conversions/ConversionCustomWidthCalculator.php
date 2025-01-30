<?php

use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Programic\MediaLibrary\Tests\TestSupport\WidthCalculators\FixedWidthCalculator;

it('can utilize various width calculators for conversions across different models', function () {
    $testModel3Sizes = (new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this->addMediaConversion('fixed_width')->withWidthCalculator(new FixedWidthCalculator([99, 60, 33]))->withResponsiveImages();
        }
    })::create(['name' => 'test.jpg']);

    $testModel5Sizes = (new class extends TestModel
    {
        public function registerMediaConversions(?Media $media = null): void
        {
            $this->addMediaConversion('fixed_width')->withWidthCalculator(new FixedWidthCalculator([76, 59, 44, 23, 11]))->withResponsiveImages();
        }
    })::create(['name' => 'test.png']);

    $testModel3Sizes->addMedia($this->getTestJpg())->toMediaCollection();

    $this->assertSame([
        '/media/1/responsive-images/test___fixed_width_99_82.jpg',
        '/media/1/responsive-images/test___fixed_width_60_49.jpg',
        '/media/1/responsive-images/test___fixed_width_33_27.jpg',
    ], $testModel3Sizes->getFirstMedia()->getResponsiveImageUrls('fixed_width'));

    $testModel5Sizes->addMedia($this->getTestPng())->toMediaCollection();

    $this->assertSame([
        '/media/2/responsive-images/test___fixed_width_76_96.jpg',
        '/media/2/responsive-images/test___fixed_width_59_74.jpg',
        '/media/2/responsive-images/test___fixed_width_44_56.jpg',
        '/media/2/responsive-images/test___fixed_width_23_29.jpg',
        '/media/2/responsive-images/test___fixed_width_11_14.jpg',
    ], $testModel5Sizes->getFirstMedia()->getResponsiveImageUrls('fixed_width'));
});
