<?php

namespace Spatie\MediaLibrary\Tests\Feature\HasMediaTrait;

use Spatie\MediaLibrary\Tests\TestCase;

class HasMediaTest extends TestCase
{
    /** @test */
    public function it_returns_false_for_an_empty_collection()
    {
        $this->assertFalse($this->testModel->hasMedia());
    }

    /** @test */
    public function it_returns_true_for_a_non_empty_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertTrue($this->testModel->hasMedia());
    }

    /** @test */
    public function it_returns_true_for_a_non_empty_collection_in_an_unsaved_model()
    {
        $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertTrue($this->testUnsavedModel->hasMedia());
    }

    /** @test */
    public function it_returns_true_if_any_collection_is_not_empty()
    {
        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue($this->testModel->hasMedia('images'));
    }

    /** @test */
    public function it_returns_false_for_an_empty_named_collection()
    {
        $this->assertFalse($this->testModel->hasMedia('images'));
    }

    /** @test */
    public function it_returns_true_for_a_non_empty_named_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue($this->testModel->hasMedia('images'));
        $this->assertFalse($this->testModel->hasMedia('downloads'));
    }

    /** @test */
    public function it_returns_true_if_it_has_any_media()
    {
        $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
        $this->assertTrue($this->testModel->hasAnyMedia());
    }

    /** @test */
    public function it_returns_false_if_it_has_no_media()
    {
        $this->assertFalse($this->testModel->hasAnyMedia());
    }
}
