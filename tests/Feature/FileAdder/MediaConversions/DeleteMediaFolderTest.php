<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    foreach (range(1, 3) as $index) {
        $this->testModelWithoutMediaConversions
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->testModelWithMultipleConversions
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
    }
});

it('will remove the media folder when deleting a media model without conversions', function () {
    $ids = $this->testModelWithoutMediaConversions->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithoutMediaConversions->clearMediaCollection();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the media folder when deleting a subject without media conversions', function () {
    $ids = $this->testModelWithoutMediaConversions->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithoutMediaConversions->delete();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the media folder when deleting a media model with conversions', function () {
    $ids = $this->testModelWithMultipleConversions->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithMultipleConversions->clearMediaCollection();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the media folder when deleting a subject with media conversions', function () {
    $ids = $this->testModelWithMultipleConversions->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithMultipleConversions->delete();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the media folder when deleting a media model with conversions and responsive images', function () {
    $ids = $this->testModelWithResponsiveImages->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithResponsiveImages->clearMediaCollection();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the media folder when deleting a subject with media conversions and responsive images', function () {
    $ids = $this->testModelWithResponsiveImages->getMedia()->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModelWithResponsiveImages->delete();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});
