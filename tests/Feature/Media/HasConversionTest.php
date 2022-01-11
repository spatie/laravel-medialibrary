<?php

it('can have a conversion', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});
