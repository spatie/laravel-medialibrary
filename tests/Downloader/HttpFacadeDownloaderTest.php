<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('can save a url to a temp location', function () {
    $url = 'https://example.com';

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

    $downloader = new \Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader;

    $result = $downloader->getTempFile($url);

    expect($result)->toBeString();
});

it('can be mocked easily for tests', function () {
    $url = 'https://example.com';

    Http::fake([
        // Stub a JSON response for GitHub endpoints...
        'https://example.com' => Http::response('::file::'),
    ]);

    $downloader = new \Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader;

    $result = $downloader->getTempFile($url);

    expect($result)
        ->toBeString()
        ->and($result)
        ->toBeFile()
        ->and(\Illuminate\Support\Facades\File::get($result))
        ->toBe('::file::');

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://example.com';
    });
});

it('respects ssl verification settings from config', function () {
    $url = 'https://example.com';

    Config::set('media-library.media_downloader_ssl', false);

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
        ->shouldReceive('withoutVerifying')
        ->once()
        ->getMock()
        ->shouldReceive('get')
        ->with($url)
        ->once();

    $downloader = new \Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader;
    $downloader->getTempFile($url);
});
