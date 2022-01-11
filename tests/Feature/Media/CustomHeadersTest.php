<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('does not set empty custom headers when saved', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $this->assertFalse($media->hasCustomProperty('custom_headers'));
    $this->assertEquals([], $media->getCustomHeaders());
});

it('can set and retrieve custom headers when explicitly added', function () {
    $headers = [
        'Header' => 'Present',
    ];

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection()
        ->setCustomHeaders($headers);

    $this->assertTrue($media->hasCustomProperty('custom_headers'));
    $this->assertEquals($headers, $media->getCustomHeaders());
});
