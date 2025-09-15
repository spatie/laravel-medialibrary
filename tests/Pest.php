<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

beforeEach(function() {
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

