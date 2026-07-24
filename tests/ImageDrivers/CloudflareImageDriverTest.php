<?php

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImageDriver;
use Spatie\MediaLibrary\MediaCollections\Exceptions\CloudflareTransformationFailed;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

beforeEach(function () {
    $this->model = TestModel::create(['name' => 'test']);
    $this->media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $this->conversion = fn (Closure $closure): Conversion => Conversion::create('cf')->manipulate($closure);
});

it('builds the transformation url in the cloudflare format', function () {
    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $conversion = ($this->conversion)(fn (CloudflareImage $image) => $image->width(300)->fit(CloudflareFit::Cover));

    expect($driver->transformationUrl($this->media, $conversion))
        ->toBe("https://cf.test/cdn-cgi/image/fit=cover,width=300/{$this->media->getFullUrl()}");
});

it('trims a trailing slash from the configured zone', function () {
    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test/']);

    $conversion = ($this->conversion)(fn (CloudflareImage $image) => $image->width(10));

    expect($driver->transformationUrl($this->media, $conversion))
        ->toStartWith('https://cf.test/cdn-cgi/image/width=10/');
});

it('throws when no zone is configured', function () {
    $driver = new CloudflareImageDriver([]);

    $driver->transformationUrl($this->media, ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)));
})->throws(CloudflareTransformationFailed::class);

it('throws when the conversion has no transformation options', function () {
    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->transformationUrl($this->media, Conversion::create('cf'));
})->throws(CloudflareTransformationFailed::class);

it('throws when cloudflare returns a failed response', function () {
    Http::fake(['*' => Http::response('nope', 500)]);

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->convert(
        $this->media,
        ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)),
        tempnam(sys_get_temp_dir(), 'cf'),
    );
})->throws(CloudflareTransformationFailed::class);

it('throws when the original lives on a private disk', function () {
    config()->set('filesystems.disks.privateDisk', [
        'driver' => 'local',
        'root' => $this->getTempDirectory('private-media'),
        'visibility' => 'private',
    ]);

    $media = $this->model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('default', 'privateDisk');

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->convert(
        $media,
        ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)),
        tempnam(sys_get_temp_dir(), 'cf'),
    );
})->throws(CloudflareTransformationFailed::class);

it('throws when the media is not an image', function () {
    $media = $this->model->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection();

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->convert(
        $media,
        ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)),
        tempnam(sys_get_temp_dir(), 'cf'),
    );
})->throws(CloudflareTransformationFailed::class);

it('asks cloudflare for the requested format through the accept header', function () {
    Http::fake(['*' => Http::response('bytes', 200)]);

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->convert(
        $this->media,
        ($this->conversion)(fn (CloudflareImage $image) => $image->format(CloudflareFormat::Webp)),
        tempnam(sys_get_temp_dir(), 'cf'),
    );

    Http::assertSent(fn ($request) => $request->header('Accept') === ['image/webp']);
});

it('keeps the original format when no format is requested', function () {
    Http::fake(['*' => Http::response('bytes', 200)]);

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);

    $driver->convert(
        $this->media,
        ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)),
        tempnam(sys_get_temp_dir(), 'cf'),
    );

    // The jpg test image reports image/jpeg, so that is what we accept.
    Http::assertSent(fn ($request) => $request->header('Accept') === ['image/jpeg']);
});

it('writes the fetched bytes to the given file', function () {
    Http::fake(['*' => Http::response('the-transformed-image', 200)]);

    $driver = new CloudflareImageDriver(['zone' => 'https://cf.test']);
    $file = tempnam(sys_get_temp_dir(), 'cf');

    $driver->convert($this->media, ($this->conversion)(fn (CloudflareImage $image) => $image->width(10)), $file);

    expect(file_get_contents($file))->toBe('the-transformed-image');

    @unlink($file);
});

it('resolves the stored extension from the format, or the original extension', function () {
    $driver = new CloudflareImageDriver([]);

    $webp = ($this->conversion)(fn (CloudflareImage $image) => $image->format(CloudflareFormat::Webp));
    $noFormat = ($this->conversion)(fn (CloudflareImage $image) => $image->width(10));

    expect($driver->conversionExtension($webp, 'jpg'))->toBe('webp')
        ->and($driver->conversionExtension($noFormat, 'png'))->toBe('png');
});
