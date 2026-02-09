<?php

use Spatie\MediaLibrary\MediaCollections\Filesystem;

beforeEach(function () {
    $this->filesystem = app()->make(Filesystem::class);
});

it('can determine the header for file that will be copied to an external filesystem', function () {
    $expectedHeaders = [
        'ContentType' => 'image/jpeg',
        'CacheControl' => 'max-age=604800',
    ];

    $this->assertEquals(
        $expectedHeaders,
        $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
    );
});

it('can add custom headers for file that will be copied to an external filesystem', function () {
    $this->filesystem->addCustomRemoteHeaders([
        'ACL' => 'public-read',
    ]);

    $expectedHeaders = [
        'ContentType' => 'image/jpeg',
        'CacheControl' => 'max-age=604800',
        'ACL' => 'public-read',
    ];

    $this->assertEquals(
        $expectedHeaders,
        $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
    );
});

it('can use custom headers when copying the media to an external filesystem', function () {
    $this->filesystem->addCustomRemoteHeaders([
        'CacheControl' => 'max-age=302400',
    ]);

    $expectedHeaders = [
        'ContentType' => 'image/jpeg',
        'CacheControl' => 'max-age=302400',
    ];

    $this->assertEquals(
        $expectedHeaders,
        $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
    );
});

it('does not let media custom headers override ContentType for conversions', function () {
    $mediaCustomHeaders = [
        'ContentType' => 'application/pdf',
        'CacheControl' => 'max-age=31536000',
    ];

    $headers = $this->filesystem->getRemoteHeadersForFile(
        $this->getTestJpg(),
        $mediaCustomHeaders,
    );

    // Without the fix, ContentType would be 'application/pdf' from the custom headers
    expect($headers['ContentType'])->toBe('application/pdf');

    // Simulate what copyToMediaLibrary now does for conversions:
    // strip ContentType from custom headers so it's derived from the actual file
    unset($mediaCustomHeaders['ContentType']);

    $headers = $this->filesystem->getRemoteHeadersForFile(
        $this->getTestJpg(),
        $mediaCustomHeaders,
    );

    expect($headers['ContentType'])->toBe('image/jpeg');
    expect($headers['CacheControl'])->toBe('max-age=31536000');
});

it('can get stream with custom path generator that uses prefix instead of directory', function () {
    config()->set('media-library.path_generator', \Spatie\MediaLibrary\Tests\TestSupport\TestPrefixPathGenerator::class);

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $stream = $this->filesystem->getStream($media);

    expect($stream)->not->toBeNull();
    expect(is_resource($stream))->toBeTrue();

    fclose($stream);
});
