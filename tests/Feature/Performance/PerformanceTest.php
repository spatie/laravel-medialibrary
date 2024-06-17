<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Facades\Config;

it('can use eagerly loaded media', function () {
    foreach (range(1, 10) as $index) {
        $testModel = $this->testModelWithConversion->create(['name' => "test{$index}"]);
        $testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    }

    DB::connection()->enableQueryLog();

    $testModels = $this->testModelWithConversion->get();
    $testModels->load('media');

    foreach ($testModels as $testModel) {
        $testModel->getFirstMediaUrl('images', 'thumb');
    }

    expect(DB::getQueryLog())->toHaveCount(2);
});

it('can lazy load media by default even prevent lazy loading globally enabled', function () {
    foreach (range(1, 10) as $index) {
        $testModel = $this->testModelWithConversion->create(['name' => "test{$index}"]);
        $testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    }

    Model::preventLazyLoading();

    DB::connection()->enableQueryLog();

    $testModels = $this->testModelWithConversion->get();

    foreach ($testModels as $testModel) {
        $testModel->getFirstMediaUrl('images', 'thumb');
    }

    expect(DB::getQueryLog())->toHaveCount(12);
});

it('throws an exception when lazy loading is disabled on both the config and globally', function () {
    foreach (range(1, 10) as $index) {
        $testModel = $this->testModelWithConversion->create(['name' => "test{$index}"]);
        $testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    }

    Model::preventLazyLoading();

    Config::set('media-library.force_lazy_loading', false);

    $testModels = $this->testModelWithConversion->get();

    expect(fn () => $testModels->first()->getFirstMediaUrl('images', 'thumb'))->toThrow(LazyLoadingViolationException::class);
});
