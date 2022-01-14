<?php

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
