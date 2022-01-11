<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can return an image as a response', function () {
    app()['router']->get('/upload', fn () => $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection());

    $result = $this->call('get', 'upload');

    $this->assertEquals(200, $result->getStatusCode());
    $result->assertHeader('Content-Type', 'image/jpeg');
    $result->assertHeader('Content-Length', 29085);
});

it('can return a text as a response', function () {
    app()['router']->get('/upload', fn () => $this->testModel
        ->addMedia($this->getTestFilesDirectory('test.txt'))
        ->toMediaCollection());

    $result = $this->call('get', 'upload');

    $this->assertEquals(200, $result->getStatusCode());
    $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $result->assertHeader('Content-Length', 45);
});
