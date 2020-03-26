<?php

namespace Spatie\MediaLibrary\Tests\Feature\InteractsWithMedia;

use DB;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class GetMediaTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_empty_collection()
    {
        $emptyCollection = $this->testModel->getMedia('images');
        $this->assertInstanceOf(Collection::class, $emptyCollection);
        $this->assertCount(0, $emptyCollection);
    }

    /** @test */
    public function it_will_only_get_media_from_the_specified_collection()
    {
        $this->assertCount(0, $this->testModel->getMedia('images'));
        $this->assertCount(0, $this->testModel->getMedia('downloads'));
        $this->assertCount(0, $this->testModel->getMedia());

        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('images');
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('downloads');
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection();

        $this->testModel = $this->testModel->fresh();

        $this->assertCount(1, $this->testModel->getMedia('images'));
        $this->assertCount(1, $this->testModel->getMedia('downloads'));
        $this->assertCount(1, $this->testModel->getMedia());
    }

    /** @test */
    public function it_returns_a_media_collection_as_a_laravel_collection()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

        $this->assertInstanceOf(Collection::class, $this->testModel->getMedia());
    }

    /** @test */
    public function it_returns_collections_filled_with_media_objects()
    {
        $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

        $this->assertInstanceOf(Media::class, $this->testModel->getMedia()->first());
    }

    /** @test */
    public function it_can_get_multiple_media_from_the_default_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

        $this->assertCount(2, $this->testModel->getMedia());
    }

    /** @test */
    public function it_can_get_multiple_media_from_the_default_collection_empty()
    {
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

        $this->assertCount(1, $this->testModel->getMedia());
        $this->assertCount(0, $this->testModel->getMedia(''));

        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('');

        $this->assertCount(1, $this->testModel->refresh()->getMedia());
        $this->assertCount(1, $this->testModel->refresh()->getMedia(''));
    }

    /** @test */
    public function it_can_get_files_from_a_named_collection()
    {
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
        $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $this->assertCount(1, $this->testModel->getMedia('images'));
        $this->assertEquals('images', $this->testModel->getMedia('images')[0]->collection_name);
    }

    /** @test */
    public function it_can_get_files_from_a_collection_using_a_filter()
    {
        $media1 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value1'])
            ->toMediaCollection();

        $media2 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value2'])
            ->toMediaCollection('images');

        $media3 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value1'])
            ->toMediaCollection('images');

        $media4 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value2'])
            ->toMediaCollection('images');

        $collection = $this->testModel->getMedia('images', ['filter2' => 'value1']);
        $this->assertCount(1, $collection);
        $this->assertSame($collection->first()->id, $media3->id);
    }

    /** @test */
    public function it_can_get_files_from_a_collection_using_a_filter_callback()
    {
        $media1 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value1'])
            ->toMediaCollection();

        $media2 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter1' => 'value2'])
            ->toMediaCollection('images');

        $media3 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value1'])
            ->toMediaCollection('images');

        $media4 = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties(['filter2' => 'value2'])
            ->toMediaCollection('images');

        $collection = $this->testModel->getMedia('images', fn (Media $media) => isset($media->custom_properties['filter1']));

        $this->assertCount(1, $collection);
        $this->assertSame($collection->first()->id, $media2->id);
    }

    /** @test */
    public function it_can_get_the_first_media_from_a_collection()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $media->name = 'first';
        $media->save();

        $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $media->name = 'second';
        $media->save();

        $this->assertEquals('first', $this->testModel->getFirstMedia('images')->name);
    }

    /** @test */
    public function it_can_get_the_first_media_from_a_collection_using_a_filter()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->withCustomProperties(['extra_property' => 'yes'])
            ->preservingOriginal()
            ->toMediaCollection('images');
        $media->name = 'first';
        $media->save();

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');
        $media->name = 'second';
        $media->save();

        $this->assertEquals('first', $this->testModel->getFirstMedia('images', ['extra_property' => 'yes'])->name);
    }

    /** @test */
    public function it_can_get_the_first_media_from_a_collection_using_a_filter_callback()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->withCustomProperties(['extra_property' => 'yes'])
            ->preservingOriginal()
            ->toMediaCollection('images');
        $media->name = 'first';
        $media->save();

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');
        $media->name = 'second';
        $media->save();

        $firstMedia = $this->testModel->getFirstMedia('images', fn (Media $media) => isset($media->custom_properties['extra_property']));

        $this->assertEquals('first', $firstMedia->name);
    }

    public function it_returns_false_when_getting_first_media_for_an_empty_collection()
    {
        $this->assertFalse($this->testModel->getFirstMedia());
    }

    /** @test */
    public function it_can_get_the_url_to_first_media_in_a_collection()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $firstMedia->save();

        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $secondMedia->save();

        $this->assertEquals($firstMedia->getUrl(), $this->testModel->getFirstMediaUrl('images'));
    }

    /** @test */
    public function it_can_get_the_path_to_first_media_in_a_collection()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $firstMedia->save();

        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $secondMedia->save();

        $this->assertEquals($firstMedia->getPath(), $this->testModel->getFirstMediaPath('images'));
    }

    /** @test */
    public function it_can_get_the_default_path_to_the_first_media_in_a_collection()
    {
        $this->assertEquals('/default.jpg', $this->testModel->getFirstMediaPath('avatar'));
    }

    /** @test */
    public function it_can_get_the_default_url_to_the_first_media_in_a_collection()
    {
        $this->assertEquals('/default.jpg', $this->testModel->getFirstMediaUrl('avatar'));
    }

    /** @test */
    public function it_will_return_preloaded_media_sorting_on_order_column()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $preloadedTestModel = TestModel::with('media')
            ->where('id', $this->testModel->id)
            ->first();

        $this->assertSame([
            1 => '1',
            2 => '2',
        ], $preloadedTestModel->getMedia('images')->pluck('order_column', 'id')->toArray());

        $firstMedia->order_column = 3;
        $firstMedia->save();

        $preloadedTestModel = TestModel::with('media')
            ->where('id', $this->testModel->id)
            ->first();

        $this->assertSame([
            2 => '2',
            1 => '3',
        ], $preloadedTestModel->getMedia('images')->pluck('order_column', 'id')->toArray());
    }

    /** @test */
    public function it_will_cache_loaded_media()
    {
        DB::enableQueryLog();

        $this->assertFalse($this->testModel->relationLoaded('media'));
        $this->assertCount(0, DB::getQueryLog());

        $this->testModel->getMedia('images');

        $this->assertTrue($this->testModel->relationLoaded('media'));
        $this->assertCount(1, DB::getQueryLog());

        $this->testModel->getMedia('images');

        $this->assertCount(1, DB::getQueryLog());

        DB::DisableQueryLog();
    }
}
