<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use Spatie\MediaLibrary\Test\TestCase;

class HasMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_false_for_an_empty_collection()
    {
        $this->assertFalse($this->testModel->hasMedia());
    }

    /**
     * @test
     */
    public function it_returns_true_for_a_non_empty_collection()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'));

        $this->assertTrue($this->testModel->hasMedia());
    }

    /**
     * @test
     */
    public function it_returns_false_for_an_empty_named_collection()
    {
        $this->assertFalse($this->testModel->hasMedia('images'));
    }

    /**
     * @test
     */
    public function it_returns_true_for_a_non_empty_named_collection()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'), 'images');

        $this->assertTrue($this->testModel->hasMedia('images'));
        $this->assertFalse($this->testModel->hasMedia('downloads'));
    }
}
