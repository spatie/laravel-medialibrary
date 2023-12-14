<?php

it('can return an image as a response', function () {
    app()['router']->get('/upload', fn () => $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection());

    $result = $this->call('get', 'upload');

    expect($result->getStatusCode())->toEqual(200);
    $result->assertHeader('Content-Type', 'image/jpeg');
    $result->assertHeader('Content-Length', 29085);
});

it('can return a text as a response', function () {
    app()['router']->get('/upload', fn () => $this->testModel
        ->addMedia($this->getTestFilesDirectory('test.txt'))
        ->toMediaCollection());

    $result = $this->call('get', 'upload');

    expect($result->getStatusCode())->toEqual(200);
    $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $result->assertHeader('Content-Length', 45);
});
