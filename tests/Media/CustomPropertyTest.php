<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class CustomPropertyTest extends TestCase
{
    protected $mediaWithCustomProperty;
    protected $mediaWithoutCustomProperty;

    public function setUp()
    {
        parent::setUp();

        $this->mediaWithCustomProperty = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['customName' => 'customValue'])
            ->toMediaLibrary('images');

        $this->mediaWithoutCustomProperty = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaLibrary('images');
    }

    /** @test */
    public function it_can_determine_if_a_media_item_has_custom_property()
    {
        $this->assertTrue($this->mediaWithCustomProperty->hasCustomProperty('customName'));
        $this->assertFalse($this->mediaWithCustomProperty->hasCustomProperty('nonExisting'));

        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('customName'));
        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('nonExisting'));
    }

    /** @test */
    public function it_can_get_a_custom_property_of_a_media_item()
    {
        $this->assertEquals('customValue', $this->mediaWithCustomProperty->getCustomProperty('customName'));
        $this->assertNull($this->mediaWithCustomProperty->getCustomProperty('nonExisting'));

        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('customName'));
        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('nonExisting'));
    }

    /** @test */
    public function it_can_set_custom_property()
    {
        $this->mediaWithCustomProperty->setCustomProperty('anotherName', 'anotherValue');

        $this->assertEquals('customValue', $this->mediaWithCustomProperty->getCustomProperty('customName'));
        $this->assertEquals('anotherValue', $this->mediaWithCustomProperty->getCustomProperty('anotherName'));
    }

    /** @test */
    public function it_returns_a_fallback_if_the_custom_property_isnt_set()
    {
        $this->assertEquals('foo', $this->mediaWithCustomProperty->getCustomProperty('imNotHere', 'foo'));
    }
}
