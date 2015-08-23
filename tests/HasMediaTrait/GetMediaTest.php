<?php

namespace Spatie\MediaLibrary\Test\HasMediaTrait;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;

class GetMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_an_empty_collection()
    {
        $emptyCollection = $this->testModel->getMedia('images');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $emptyCollection);
        $this->assertCount(0, $emptyCollection);
    }

    /**
     * @test
     */
    public function it_will_get_all_media_when_not_specify_a_collection()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toCollection('images');
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toCollection('downloads');
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaLibrary();

        $this->assertCount(3, $this->testModel->getMedia());
    }

    /**
     * @test
     */
    public function it_returns_a_media_collection_as_a_laravel_collection()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaLibrary();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $this->testModel->getMedia());
    }

    /**
     * @test
     */
    public function it_returns_collections_filled_with_media_objects()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaLibrary();

        $this->assertInstanceOf(\Spatie\MediaLibrary\Media::class, $this->testModel->getMedia()->first());
    }

    /**
     * @test
     */
    public function it_can_get_multiple_media_from_the_default_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaLibrary();
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaLibrary();

        $this->assertCount(2, $this->testModel->getMedia());
    }

    /**
     * @test
     */
    public function it_can_get_files_from_a_named_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaLibrary();
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');

        $this->assertCount(1, $this->testModel->getMedia('images'));
        $this->assertEquals('images', $this->testModel->getMedia('images')[0]->collection_name);
    }

    /**
     * @test
     */
    public function it_can_get_files_from_a_collection_using_a_filter()
    {
        $media1 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value1'])
            ->toMediaLibrary();

        $media2 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value2'])
            ->toMediaLibrary('images');

        $media3 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value1'])
            ->toMediaLibrary('images');

        $media4 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value2'])
            ->toMediaLibrary('images');

        $collection = $this->testModel->getMedia('images', ['filter2' => 'value1']);
        $this->assertCount(1, $collection);
        $this->assertTrue($collection->first()->id == $media3->id);
    }

    /**
     * @test
     */
    public function it_can_get_files_from_a_collection_using_a_filter_callback()
    {
        $media1 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value1'])
            ->toMediaLibrary();

        $media2 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value2'])
            ->toMediaLibrary('images');

        $media3 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value1'])
            ->toMediaLibrary('images');

        $media4 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value2'])
            ->toMediaLibrary('images');

        $collection = $this->testModel->getMedia('images', function (Media $media) {
            return isset($media->custom_properties['filter1']);
        });

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->first()->id == $media2->id);
    }

    /**
     * @test
     */
    public function it_can_get_the_first_media_from_a_collection()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $media->name = 'first';
        $media->save();

        $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $media->name = 'second';
        $media->save();

        $this->assertEquals('first', $this->testModel->getFirstMedia('images')->name);
    }

    /**
     * @test
     */
    public function it_can_get_the_first_media_from_a_collection_using_a_filter()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->withCustomProperties(['extra_property' => 'yes'])
            ->preservingOriginal()
            ->toCollection('images');
        $media->name = 'first';
        $media->save();

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toCollection('images');
        $media->name = 'second';
        $media->save();

        $this->assertEquals('first', $this->testModel->getFirstMedia('images', ['extra_property' => 'yes'])->name);
    }

    public function it_returns_false_when_getting_first_media_for_an_empty_collection()
    {
        $this->assertFalse($this->testModel->getFirstMedia());
    }

    /**
     * @test
     */
    public function it_can_get_the_url_to_first_media_in_a_collection()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $firstMedia->save();

        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $secondMedia->save();

        $this->assertEquals($firstMedia->getUrl(), $this->testModel->getFirstMediaUrl('images'));
    }

    /**
     * @test
     */
    public function it_can_get_the_path_to_first_media_in_a_collection()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $firstMedia->save();

        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toCollection('images');
        $secondMedia->save();

        $this->assertEquals($firstMedia->getPath(), $this->testModel->getFirstMediaPath('images'));
    }
}
