<?php

use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

beforeEach(function () {
    // Clear any existing environment variables
    putenv('AWS_DEFAULT_REGION');
    putenv('AWS_REGION');
    unset($_ENV['AWS_DEFAULT_REGION'], $_ENV['AWS_REGION']);
    unset($_SERVER['AWS_DEFAULT_REGION'], $_SERVER['AWS_REGION']);
});

afterEach(function () {
    // Clean up environment variables
    putenv('AWS_DEFAULT_REGION');
    putenv('AWS_REGION');
    unset($_ENV['AWS_DEFAULT_REGION'], $_ENV['AWS_REGION']);
    unset($_SERVER['AWS_DEFAULT_REGION'], $_SERVER['AWS_REGION']);
});

function callEnsureAwsDefaultRegionFallback($app): void
{
    $provider = new MediaLibraryServiceProvider($app);
    $method = new \ReflectionMethod($provider, 'ensureAwsDefaultRegionFallback');
    $method->setAccessible(true);
    $method->invoke($provider);
}

it('does not modify s3 disk configuration when AWS_DEFAULT_REGION is set', function () {
    putenv('AWS_DEFAULT_REGION=us-east-1');
    $_ENV['AWS_DEFAULT_REGION'] = 'us-east-1';
    $_SERVER['AWS_DEFAULT_REGION'] = 'us-east-1';

    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-west-2',
        'bucket' => 'test-bucket',
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('us-west-2');
});

it('does not modify s3 disk configuration when AWS_REGION is not set', function () {
    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-west-2',
        'bucket' => 'test-bucket',
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('us-west-2');
});

it('updates s3 disk configuration with region from AWS_REGION when AWS_DEFAULT_REGION is not set', function () {
    putenv('AWS_REGION=eu-west-1');
    $_ENV['AWS_REGION'] = 'eu-west-1';
    $_SERVER['AWS_REGION'] = 'eu-west-1';

    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'bucket' => 'test-bucket',
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('eu-west-1');
});

it('updates multiple s3 disk configurations with region from AWS_REGION', function () {
    putenv('AWS_REGION=ap-southeast-1');
    $_ENV['AWS_REGION'] = 'ap-southeast-1';
    $_SERVER['AWS_REGION'] = 'ap-southeast-1';

    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'bucket' => 'test-bucket',
    ]);

    Config::set('filesystems.disks.s3_backup', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'bucket' => 'test-bucket-backup',
    ]);

    Config::set('filesystems.disks.local_disk', [
        'driver' => 'local',
        'root' => storage_path('app'),
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('ap-southeast-1');
    expect(Config::get('filesystems.disks.s3_backup.region'))->toBe('ap-southeast-1');
    expect(Config::get('filesystems.disks.local_disk'))->not->toHaveKey('region');
});

it('does not override existing region in s3 disk configuration', function () {
    putenv('AWS_REGION=us-east-1');
    $_ENV['AWS_REGION'] = 'us-east-1';
    $_SERVER['AWS_REGION'] = 'us-east-1';

    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-west-2',
        'bucket' => 'test-bucket',
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('us-west-2');
});

it('only updates s3 disks and ignores other disk types', function () {
    putenv('AWS_REGION=eu-central-1');
    $_ENV['AWS_REGION'] = 'eu-central-1';
    $_SERVER['AWS_REGION'] = 'eu-central-1';

    Config::set('filesystems.disks.s3_disk', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'bucket' => 'test-bucket',
    ]);

    Config::set('filesystems.disks.local_disk', [
        'driver' => 'local',
        'root' => storage_path('app'),
    ]);

    Config::set('filesystems.disks.ftp_disk', [
        'driver' => 'ftp',
        'host' => 'ftp.example.com',
    ]);

    callEnsureAwsDefaultRegionFallback($this->app);

    expect(Config::get('filesystems.disks.s3_disk.region'))->toBe('eu-central-1');
    expect(Config::get('filesystems.disks.local_disk'))->not->toHaveKey('region');
    expect(Config::get('filesystems.disks.ftp_disk'))->not->toHaveKey('region');
});

