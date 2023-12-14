<?php

it('does not set empty custom headers when saved', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    expect($media->hasCustomProperty('custom_headers'))->toBeFalse();
    expect($media->getCustomHeaders())->toEqual([]);
});

it('can set and retrieve custom headers when explicitly added', function () {
    $headers = [
        'Header' => 'Present',
    ];

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection()
        ->setCustomHeaders($headers);

    expect($media->hasCustomProperty('custom_headers'))->toBeTrue();
    expect($media->getCustomHeaders())->toEqual($headers);
});
