<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

use Spatie\MediaLibrary\MediaCollections\FileAdder;

it('sanitizes filenames correctly', function () {
    /** @var FileAdder $adder */
    $adder = app(FileAdder::class);

    expect($adder->defaultSanitizer('valid-filename.jpg'))
        ->toEqual('valid-filename.jpg');

    expect($adder->defaultSanitizer('test one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test    one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test one 😀.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test/\/\one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test|one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test#one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('some/test/file.pdf'))
        ->toEqual('some-test-file.pdf');

    expect($adder->defaultSanitizer('Scan-‎9‎.‎14‎.‎2022-‎7‎.‎23‎.‎28.pdf'))
        ->toEqual('Scan-9.14.2022-7.23.28.pdf');
});
