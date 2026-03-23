<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader;

it('can save a url to a temp location', function () {
    $url = 'https://example.com';

    Http::shouldReceive('withUserAgent')
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

    $downloader = new HttpFacadeDownloader;

    $result = $downloader->getTempFile($url);

    expect($result)->toBeString();
});

it('can be mocked easily for tests', function () {
    $url = 'https://example.com';

    Http::fake([
        // Stub a JSON response for GitHub endpoints...
        'https://example.com' => Http::response('::file::'),
    ]);

    $downloader = new HttpFacadeDownloader;

    $result = $downloader->getTempFile($url);

    expect($result)
        ->toBeString()
        ->and($result)
        ->toBeFile()
        ->and(File::get($result))
        ->toBe('::file::');

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://example.com';
    });
});
