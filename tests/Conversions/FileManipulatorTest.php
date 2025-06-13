<?php

use Spatie\MediaLibrary\Conversions\Actions\PerformManipulationsAction;
use Spatie\MediaLibrary\Conversions\Conversion;

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
