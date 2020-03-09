<?php

namespace Spatie\MediaLibrary\Tests\Feature\Performance;

use DB;
use Spatie\MediaLibrary\Tests\TestCase;

class PerformanceTest extends TestCase
{
    /** @test */
    public function it_can_use_eagerly_loaded_media()
    {
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

        $this->assertCount(2, DB::getQueryLog());
    }
}
