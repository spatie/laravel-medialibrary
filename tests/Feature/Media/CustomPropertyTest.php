<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;

class CustomPropertyTest extends TestCase
{
    protected Media $media;

    protected Media $mediaWithoutCustomProperty;

    public function setUp(): void
    {
        parent::setUp();

        $this->media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties([
                'customName' => 'customValue',
                'nested' => [
                    'customName' => 'nested customValue',
                ],
            ])
            ->toMediaCollection('images');
    }

    /** @test */
    public function it_can_determine_if_a_media_item_has_a_custom_property()
    {
        $this->assertTrue($this->media->hasCustomProperty('customName'));
        $this->assertTrue($this->media->hasCustomProperty('nested.customName'));

        $this->assertFalse($this->media->hasCustomProperty('nonExisting'));
        $this->assertFalse($this->media->hasCustomProperty('nested.nonExisting'));
    }

    /** @test */
    public function it_can_get_a_custom_property()
    {
        $this->assertEquals('customValue', $this->media->getCustomProperty('customName'));
        $this->assertEquals('nested customValue', $this->media->getCustomProperty('nested.customName'));

        $this->assertNull($this->media->getCustomProperty('nonExisting'));
        $this->assertNull($this->media->getCustomProperty('nested.nonExisting'));
    }

    /** @test */
    public function it_can_set_a_custom_property()
    {
        $this->media->setCustomProperty('anotherName', 'anotherValue');

        $this->assertEquals('anotherValue', $this->media->getCustomProperty('anotherName'));
        $this->assertEquals('customValue', $this->media->getCustomProperty('customName'));

        $this->media->setCustomProperty('nested.anotherName', 'anotherValue');
        $this->assertEquals('anotherValue', $this->media->getCustomProperty('nested.anotherName'));
    }

    /** @test */
    public function it_can_forget_a_custom_property()
    {
        $this->assertTrue($this->media->hasCustomProperty('customName'));
        $this->assertTrue($this->media->hasCustomProperty('nested.customName'));

        $this->media->forgetCustomProperty('customName');
        $this->media->forgetCustomProperty('nested.customName');

        $this->assertFalse($this->media->hasCustomProperty('customName'));
        $this->assertFalse($this->media->hasCustomProperty('nested.customName'));
    }

    /** @test */
    public function it_returns_a_fallback_if_a_custom_property_isnt_set()
    {
        $this->assertEquals('foo', $this->media->getCustomProperty('imNotHere', 'foo'));
    }
}
