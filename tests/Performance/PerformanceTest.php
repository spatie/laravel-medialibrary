<?php

namespace Spatie\MediaLibrary\Test\HasMediaWithoutConversionsTrait;

use Spatie\MediaLibrary\Test\TestCase;

class PerformanceTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_not_reload_media_if_it_is_eagerly_loaded()
    {
        $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'));

        \DB::connection()->enableQueryLog();

        $testModels = $this->testModelWithConversion->get();
        $testModels->load("media");

        $testModels[0]->getFirstMediaUrl();
        $testModels[0]->getFirstMediaUrl();
        $testModels[0]->getFirstMediaUrl();
        $testModels[0]->getFirstMediaUrl();

        $this->assertCount(2, \DB::getQueryLog());
    }
}