<?php

use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithoutMediaConversions;

uses(TestCase::class);

beforeEach(function () {
    foreach (range(1, 3) as $index) {
        $this->testModelWithoutMediaConversions
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->testModelWithoutMediaConversions
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');
    }
});

it('can clear a collection', function () {
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));

    $this->testModelWithoutMediaConversions->clearMediaCollection('images');
    $this->testModelWithoutMediaConversions = $this->testModelWithoutMediaConversions->fresh();

    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertCount(0, $this->testModelWithoutMediaConversions->getMedia('images'));
});

it('can clear the default collection', function () {
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));

    $this->testModelWithoutMediaConversions->clearMediaCollection();
    $this->testModelWithoutMediaConversions = $this->testModelWithoutMediaConversions->fresh();

    $this->assertCount(0, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));
});

it('can clear a collection excluding a single media', function () {
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));

    $excludedMedia = $this->testModelWithoutMediaConversions->getFirstMedia('images');

    $this->testModelWithoutMediaConversions->clearMediaCollectionExcept('images', $excludedMedia);

    $this->assertEquals($this->testModelWithoutMediaConversions->getMedia('images')[0], $excludedMedia);
    $this->assertCount(1, $this->testModelWithoutMediaConversions->getMedia('images'));
});

it('can clear a collection excluding some media', function () {
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));

    $excludedMedia = $this->testModelWithoutMediaConversions->getMedia('images')->take(2);

    $this->testModelWithoutMediaConversions->clearMediaCollectionExcept('images', $excludedMedia);
    $this->testModelWithoutMediaConversions = $this->testModelWithoutMediaConversions->fresh();

    $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
    $this->assertEquals($this->testModelWithoutMediaConversions->getMedia('images')[0], $excludedMedia[0]);
    $this->assertEquals($this->testModelWithoutMediaConversions->getMedia('images')[1], $excludedMedia[1]);
});

it('provides a chainable method for clearing a collection', function () {
    $result = $this->testModelWithoutMediaConversions->clearMediaCollection('images');

    $this->assertInstanceOf(TestModelWithoutMediaConversions::class, $result);
});

it('will remove the files when clearing a collection', function () {
    $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModelWithoutMediaConversions->clearMediaCollection('images');

    $ids->map(function ($id) {
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
    });
});

it('will remove the files when deleting a subject without media conversions', function () {
    $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModelWithoutMediaConversions->delete();

    $ids->map(function ($id) {
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
    });
});

it('will not remove the files when deleting a subject and preserving media', function () {
    $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModelWithoutMediaConversions->deletePreservingMedia();

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });
});
