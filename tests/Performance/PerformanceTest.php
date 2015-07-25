<?php

namespace Spatie\MediaLibrary\Test\HasMediaWithoutConversionsTrait;

use DB;
use Spatie\MediaLibrary\Test\TestCase;

class PerformanceTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_not_reload_media_if_it_is_eagerly_loaded()
    {
        foreach (range(1, 10) as $index) {
            $testModel = $this->testModelWithConversion->create(['name' => "test{$index}"]);
            $testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'default', false);
        }

        DB::connection()->enableQueryLog();

        $testModels = $this->testModelWithConversion->get();
        $testModels->load("media");

        foreach ($testModels as $testModel) {
            $testModel->getFirstMediaUrl();
        }

        $this->assertCount(2, DB::getQueryLog());
    }
}
