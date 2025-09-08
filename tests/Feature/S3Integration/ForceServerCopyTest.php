<?php

use Mockery as m;
use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\RemoteFile;

function callProtected(object $obj, string $method, array $args = [])
{
    $ref = new ReflectionClass($obj);
    $m = $ref->getMethod($method);
    $m->setAccessible(true);
    return $m->invokeArgs($obj, $args);
}

beforeEach(function () {
    $base = config('filesystems.disks.s3_disk') ?? [];
    config()->set('filesystems.disks.s3_disk', array_merge($base, [
        'driver' => 's3',
        'force_server_copy' => false,
    ]));
});

afterEach(function () {
    m::close();
});

it('returns true when force_server_copy is enabled (same disk, non-local, even with headers)', function () {
    config()->set('filesystems.disks.s3_disk.force_server_copy', true);

    $fs = m::mock(Factory::class);
    $filesystem = new Filesystem($fs);

    $file = m::mock(RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('s3_disk');
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(Media::class)->makePartial();
    $media->disk = 's3_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getConversionsDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getCustomHeaders')->andReturn(['ACL' => 'public-read']);

    $result = callProtected($filesystem, 'shouldCopyFileOnDisk', [$file, $media, 's3']);
    expect($result)->toBeTrue();
});

it('returns false when force_server_copy is disabled and headers exist', function () {
    $fs = m::mock(Factory::class);
    $filesystem = new Filesystem($fs);

    $file = m::mock(RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('s3_disk');
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(Media::class)->makePartial();
    $media->disk = 's3_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getConversionsDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getCustomHeaders')->andReturn(['ACL' => 'public-read']);

    $result = callProtected($filesystem, 'shouldCopyFileOnDisk', [$file, $media, 's3']);
    expect($result)->toBeFalse();
});

it('returns false when source and destination disks differ, even if force_server_copy is enabled', function () {
    config()->set('filesystems.disks.s3_disk.force_server_copy', true);

    $fs = m::mock(Factory::class);
    $filesystem = new Filesystem($fs);

    $file = m::mock(RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('another_s3_disk');
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(Media::class)->makePartial();
    $media->disk = 's3_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('s3');

    $result = callProtected($filesystem, 'shouldCopyFileOnDisk', [$file, $media, 's3']);
    expect($result)->toBeFalse();
});

it('returns true for local driver regardless', function () {
    $fs = m::mock(Factory::class);
    $filesystem = new Filesystem($fs);

    $file = m::mock(RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('local_disk');
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(Media::class)->makePartial();
    $media->disk = 'local_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('local');

    $result = callProtected($filesystem, 'shouldCopyFileOnDisk', [$file, $media, 'local']);
    expect($result)->toBeTrue();
});

it('performs server-side copy when force_server_copy is enabled on same disk', function () {
    config()->set('filesystems.disks.s3_disk.force_server_copy', true);

    $fs = m::mock(Factory::class);
    $diskMock = m::mock();

    $fs->shouldReceive('disk')->with('s3_disk')->andReturn($diskMock);
    $diskMock->shouldReceive('copy')
        ->once()
        ->with('source/key.jpg', m::on(fn ($dest) => str_ends_with($dest, '/file.jpg')));
    $diskMock->shouldNotReceive('getDriver');

    $filesystem = new class($fs) extends Filesystem {
        public function getMediaDirectory(Media $media, ?string $type = null): string
        {
            return 'media/dir/';
        }
    };

    $file = m::mock(RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('s3_disk');
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(Media::class)->makePartial();
    $media->disk = 's3_disk';
    $media->conversions_disk = 's3_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getConversionsDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getCustomHeaders')->andReturn([]);

    $filesystem->copyToMediaLibraryFromRemote($file, $media);
});

it('streams when force_server_copy is disabled and headers exist', function () {
    config()->set('filesystems.disks.s3_disk.force_server_copy', false);

    $fs = m::mock(Factory::class);
    $targetDisk = m::mock();
    $targetDriver = m::mock();

    $readStream = fopen('php://temp', 'w+');
    fwrite($readStream, 'payload');
    rewind($readStream);

    // Subclass to avoid hitting Storage::disk(...)->getDriver()->readStream(...)
    $filesystem = new class($fs, $readStream) extends Filesystem {
        private $testStream;
        public function __construct($fs, $stream) { parent::__construct($fs); $this->testStream = $stream; }
        public function getMediaDirectory(\Spatie\MediaLibrary\MediaCollections\Models\Media $media, ?string $type = null): string
        {
            return 'media/dir/';
        }
        public function copyToMediaLibraryFromRemote(\Spatie\MediaLibrary\Support\RemoteFile $file, \Spatie\MediaLibrary\MediaCollections\Models\Media $media, ?string $type = null, ?string $targetFileName = null): void
        {
            $destinationFileName = $targetFileName ?: $file->getFilename();
            $destination = $this->getMediaDirectory($media, $type) . $destinationFileName;

            $diskDriverName = (in_array($type, ['conversions', 'responsiveImages']))
                ? $media->getConversionsDiskDriverName()
                : $media->getDiskDriverName();

            // Assert branch: should be FALSE (streaming)
            $shouldCopy = (function ($file, $media, $diskDriverName) {
                $ref = new \ReflectionClass(\Spatie\MediaLibrary\MediaCollections\Filesystem::class);
                $m = $ref->getMethod('shouldCopyFileOnDisk');
                $m->setAccessible(true);
                return $m->invoke($this, $file, $media, $diskDriverName);
            })($file, $media, $diskDriverName);

            expect($shouldCopy)->toBeFalse();

            $headers = ['ContentType' => 'image/jpeg']; // minimal headers for writeStream
            $this->streamFileToDisk($this->testStream, $destination, $media->disk, $headers);
        }
    };

    $fs->shouldReceive('disk')->with('s3_disk')->andReturn($targetDisk);
    $targetDisk->shouldReceive('getDriver')->andReturn($targetDriver);
    $targetDriver->shouldReceive('writeStream')->once();

    $file = m::mock(\Spatie\MediaLibrary\Support\RemoteFile::class);
    $file->shouldReceive('getDisk')->andReturn('s3_disk'); // same disk to engage decision logic
    $file->shouldReceive('getKey')->andReturn('source/key.jpg');
    $file->shouldReceive('getFilename')->andReturn('file.jpg');

    $media = m::mock(\Spatie\MediaLibrary\MediaCollections\Models\Media::class)->makePartial();
    $media->disk = 's3_disk';
    $media->conversions_disk = 's3_disk';
    $media->shouldReceive('getDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getConversionsDiskDriverName')->andReturn('s3');
    $media->shouldReceive('getCustomHeaders')->andReturn(['ACL' => 'public-read']);

    $filesystem->copyToMediaLibraryFromRemote($file, $media);

    if (is_resource($readStream)) {
        fclose($readStream);
    }
});

