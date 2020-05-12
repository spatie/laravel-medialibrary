<?php

namespace Spatie\MediaLibrary\Tests\Feature\InteractsWithMedia;

use Spatie\MediaLibrary\Tests\TestCase;

class UpdateMediaTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel->addMedia($this->getTestJpg())->usingName('test1')->preservingOriginal()->toMediaCollection();
        $this->testModel->addMedia($this->getTestJpg())->usingName('test2')->preservingOriginal()->toMediaCollection();
    }

    /** @test */
    public function it_removes_a_media_item_if_its_not_in_the_update_array()
    {
        $mediaArray = $this->testModel->media->toArray();
        unset($mediaArray[0]);

        $this->testModel->updateMedia($mediaArray);
        $this->testModel->load('media');

        $this->assertCount(1, $this->testModel->media);
        $this->assertEquals('test2', $this->testModel->getFirstMedia()->name);
    }

    /** @test */
    public function it_removes_a_media_item_with_eager_loaded_relation()
    {
        $mediaArray = $this->testModel->media->toArray();
        unset($mediaArray[0]);

        $this->testModel->load('media');
        $this->testModel->updateMedia($mediaArray);

        $this->assertCount(1, $this->testModel->media);
        $this->assertEquals('test2', $this->testModel->getFirstMedia()->name);
    }

    /** @test */
    public function it_renames_media_items()
    {
        $mediaArray = $this->testModel->media->toArray();

        $mediaArray[0]['name'] = 'testFoo';
        $mediaArray[1]['name'] = 'testBar';

        $this->testModel->updateMedia($mediaArray);
        $this->testModel->load('media');

        $this->assertEquals('testFoo', $this->testModel->media[0]->name);
        $this->assertEquals('testBar', $this->testModel->media[1]->name);
    }

    /** @test */
    public function it_updates_media_item_custom_properties()
    {
        $mediaArray = $this->testModel->media->toArray();

        $mediaArray[0]['custom_properties']['foo'] = 'bar';

        $this->testModel->updateMedia($mediaArray);
        $this->testModel->load('media');

        $this->assertEquals('bar', $this->testModel->media[0]->getCustomProperty('foo'));
    }

    /**
     * @test
     */
    public function it_reorders_media_items()
    {
        $mediaArray = $this->testModel->media->toArray();

        $differentOrder = array_reverse($mediaArray);

        $this->testModel->updateMedia($differentOrder);
        $this->testModel->load('media');

        $orderedMedia = $this->testModel->media->sortBy('order_column');

        $this->assertEquals($mediaArray[0]['order_column'], $orderedMedia[1]->order_column);
        $this->assertEquals($mediaArray[1]['order_column'], $orderedMedia[0]->order_column);
    }
}
