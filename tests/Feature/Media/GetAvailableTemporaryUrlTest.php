<?php

use Carbon\Carbon;
use Spatie\MediaLibrary\Tests\Feature\S3Integration\S3TestPathGenerator;

beforeEach(function () {
    if (! canTestS3()) {
        $this->markTestSkipped('Skipping S3 tests because AWS environment variables are not configured (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET)');
    }

    $this->s3BaseDirectory = getS3BaseTestDirectory();

    config()->set('media-library.path_generator', S3TestPathGenerator::class);
});

afterEach(function () {
    if (! canTestS3()) {
        return;
    }

    cleanUpS3();

    config()->set('media-library.path_generator', null);
});

it('can get a temporary url of first available conversion', function () {
    $media = $this->testModelWithMultipleConversions->addMedia($this->getTestJpg())->toMediaCollection('default', 's3_disk');

    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionGenerated('large');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/conversions/test-large.jpg",
        $media->getAvailableTemporaryUrl(Carbon::now()->addMinutes(5), ['small', 'medium', 'large'], [])
    );

    $media->markAsConversionGenerated('medium');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/conversions/test-medium.jpg",
        $media->getAvailableTemporaryUrl(Carbon::now()->addMinutes(5), ['small', 'medium', 'large'], [])
    );

    $media->markAsConversionGenerated('small');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/conversions/test-small.jpg",
        $media->getAvailableTemporaryUrl(Carbon::now()->addMinutes(5), ['small', 'medium', 'large'], [])
    );
});

it('uses original url if no conversion has been generated yet', function () {
    $media = $this->testModelWithMultipleConversions->addMedia($this->getTestJpg())->toMediaCollection('default', 's3_disk');
    $media->markAsConversionNotGenerated('small');
    $media->markAsConversionNotGenerated('medium');
    $media->markAsConversionNotGenerated('large');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
        $media->getAvailableTemporaryUrl(Carbon::now()->addMinutes(5), ['small', 'medium', 'large'], [])
    );
});