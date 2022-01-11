<?php



test('test', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestJpg())->toMediaCollection();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
});
