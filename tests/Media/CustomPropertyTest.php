<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class CustomPropertyTest extends TestCase
{
    /** @var \Spatie\MediaLibrary\Media */
    protected $mediaWithCustomProperty;

    /** @var \Spatie\MediaLibrary\Media */
    protected $mediaWithoutCustomProperty;

    public function setUp()
    {
        parent::setUp();

        $this->mediaWithCustomProperty = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties([
                'customName' => 'customValue',
                'nested' => [
                    'customName' => 'customValue',
                ],
            ])
            ->toMediaLibrary('images');

        $this->mediaWithoutCustomProperty = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaLibrary('images');
    }

    /** @test */
    public function it_can_determine_if_a_media_item_has_a_custom_property()
    {
        $this->assertTrue($this->mediaWithCustomProperty->hasCustomProperty('customName'));
        $this->assertFalse($this->mediaWithCustomProperty->hasCustomProperty('nonExisting'));

        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('customName'));
        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('nonExisting'));
    }

    /** @test */
    public function it_can_determine_if_a_media_item_has_a_nested_custom_property()
    {
        $this->assertTrue($this->mediaWithCustomProperty->hasCustomProperty('nested.customName'));
        $this->assertFalse($this->mediaWithCustomProperty->hasCustomProperty('nested.nonExisting'));

        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('nested.customName'));
        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('nested.nonExisting'));
    }

    /** @test */
    public function it_can_get_a_custom_property()
    {
        $this->assertEquals('customValue', $this->mediaWithCustomProperty->getCustomProperty('customName'));
        $this->assertNull($this->mediaWithCustomProperty->getCustomProperty('nonExisting'));

        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('customName'));
        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('nonExisting'));
    }

    /** @test */
    public function it_can_get_a_nested_custom_property_using_dot_notation()
    {
        $this->assertEquals(
            'customValue',
            $this->mediaWithCustomProperty->getCustomProperty('nested.customName')
        );

        $this->assertNull($this->mediaWithCustomProperty->getCustomProperty('nested.notExisting'));

        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('nested.customName'));
        $this->assertNull($this->mediaWithoutCustomProperty->getCustomProperty('nested.notExisting'));
    }

    /** @test */
    public function it_can_set_a_custom_property()
    {
        $this->mediaWithCustomProperty->setCustomProperty('anotherName', 'anotherValue');

        $this->assertEquals('anotherValue', $this->mediaWithCustomProperty->getCustomProperty('anotherName'));
        $this->assertEquals('customValue', $this->mediaWithCustomProperty->getCustomProperty('customName'));
    }

    /** @test */
    public function it_can_set_a_nested_set_custom_property_using_dot_notation()
    {
        $this->mediaWithCustomProperty->setCustomProperty('nested.anotherName', 'anotherValue');

        $this->assertEquals('customValue', $this->mediaWithCustomProperty->getCustomProperty('nested.customName'));
        $this->assertEquals('anotherValue', $this->mediaWithCustomProperty->getCustomProperty('nested.anotherName'));
    }

    /** @test */
    public function it_can_forget_a_custom_property()
    {
        $this->mediaWithCustomProperty->forgetCustomProperty('customName');

        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('customName'));
    }

    /** @test */
    public function it_can_forget_a_nested_custom_property_using_dot_notation()
    {
        $this->mediaWithCustomProperty->forgetCustomProperty('nested.customName');

        $this->assertFalse($this->mediaWithoutCustomProperty->hasCustomProperty('nested.customName'));
    }

    /** @test */
    public function it_returns_a_fallback_if_a_custom_property_isnt_set()
    {
        $this->assertEquals('foo', $this->mediaWithCustomProperty->getCustomProperty('imNotHere', 'foo'));
    }

    /** @test */
    public function it_returns_a_fallback_if_a_nested_custom_property_isnt_set()
    {
        $this->assertEquals('foo', $this->mediaWithCustomProperty->getCustomProperty('nested.imNotHere', 'foo'));
    }
}
