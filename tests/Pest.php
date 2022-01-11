<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

expect()->extend('toHaveExtension', function (string $expectedExtension) {
    $actualExtension = pathinfo($this->value, PATHINFO_EXTENSION);

    expect($actualExtension)->toEqual($expectedExtension);
});
