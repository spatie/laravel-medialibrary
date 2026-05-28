<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

beforeEach(function () {
    registerSpatiePestHelpers();
});

expect()->extend('toHaveExtension', function (string $expectedExtension) {
    $actualExtension = pathinfo($this->value, PATHINFO_EXTENSION);

    expect($actualExtension)->toEqual($expectedExtension);
});

function assertS3FileExists(string $filePath): void
{
    expect(Storage::disk('s3_disk')->exists($filePath))->toBeTrue();
}

function assertS3FileNotExists(string $filePath): void
{
    expect(Storage::disk('s3_disk')->exists($filePath))->toBeFalse();
}

function canTestS3(): bool
{
    return ! empty(getenv('AWS_ACCESS_KEY_ID'))
        && ! empty(getenv('AWS_SECRET_ACCESS_KEY'))
        && ! empty(getenv('AWS_DEFAULT_REGION'))
        && ! empty(getenv('AWS_BUCKET'));
}

function getS3BaseTestDirectory(): string
{
    static $uuid = null;

    if (is_null($uuid)) {
        $uuid = Str::uuid();
    }

    return $uuid;
}

function s3BaseUrl(): string
{
    return 'https://laravel-medialibrary-tests.s3.eu-west-1.amazonaws.com';
}

/*
 * Strips the parts of a presigned S3 URL that depend on the moment the URL was
 * signed, so two URLs generated milliseconds apart can be compared for equality
 * without the test failing when a wall-clock second ticks between them.
 */
function s3UrlWithoutTimingParams(string $url): string
{
    [$base, $query] = array_pad(explode('?', $url, 2), 2, '');

    parse_str($query, $params);

    foreach (['X-Amz-Date', 'X-Amz-Expires', 'X-Amz-Signature'] as $param) {
        unset($params[$param]);
    }

    return $params === [] ? $base : $base.'?'.http_build_query($params);
}

function cleanUpS3(): void
{
    collect(Storage::disk('s3_disk')->allDirectories(getS3BaseTestDirectory()))
        ->each(function ($directory) {
            Storage::disk('s3_disk')->deleteDirectory($directory);
        });
}

function unserializeAndSerializeModel($model)
{
    return unserialize(serialize($model));
}

function skipWhenRunningOnGitHub(): void
{
    if (getenv('GITHUB_ACTIONS') !== false) {
        test()->markTestSkipped('This test cannot run on GitHub actions');
    }
}

function skipWhenRunningLocally(): void
{
    if (getenv('GITHUB_ACTIONS') === false) {
        test()->markTestSkipped('This test cannot run locally');
    }
}
