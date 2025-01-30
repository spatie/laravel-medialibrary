<?php

namespace Programic\MediaLibrary\Tests\MediaCollections;

use Programic\MediaLibrary\MediaCollections\Exceptions\FileNameNotAllowed;
use Programic\MediaLibrary\MediaCollections\FileAdder;

it('sanitizes filenames correctly', function () {
    /** @var FileAdder $adder */
    $adder = app(FileAdder::class);

    expect($adder->defaultSanitizer('valid-filename.jpg'))
        ->toEqual('valid-filename.jpg');

    expect($adder->defaultSanitizer('test one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('test#one.pdf'))
        ->toEqual('test-one.pdf');

    expect($adder->defaultSanitizer('some/test/file.pdf'))
        ->toEqual('some-test-file.pdf');

    expect($adder->defaultSanitizer('Scan-‎9‎.‎14‎.‎2022-‎7‎.‎23‎.‎28.pdf'))
        ->toEqual('Scan-9.14.2022-7.23.28.pdf');
});

it('will throw an exception if the sanitized file name is a php file name', function () {
    $adder = app(FileAdder::class);

    $adder->defaultSanitizer('filename.php‎');
})->throws(FileNameNotAllowed::class);

it('will not throw an exception if the sanitized file name ends with php but is not a php file', function () {
    $adder = app(FileAdder::class);

    $adder->defaultSanitizer('media-libraryJQwPHp');
})->throwsNoExceptions();
