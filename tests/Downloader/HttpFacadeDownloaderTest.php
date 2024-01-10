<?php

it('can save a url to a temp location', function () {
    $url = '';

    \Illuminate\Support\Facades\Http::shouldReceive('withUserAgent')
        ->with('Spatie MediaLibrary')
        ->once()
        ->andReturnSelf()
        ->getMock()
        ->shouldReceive('throw')
        ->once()
        ->andReturnSelf()
        ->getMock()
        ->shouldReceive('sink')
        ->once()
        ->andReturnSelf()
        ->getMock()
        ->shouldReceive('get')
        ->with($url)
        ->once();

    $downloader = new \Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader();

    $result = $downloader->getTempFile($url);

    expect($result)->toBeString();
});
