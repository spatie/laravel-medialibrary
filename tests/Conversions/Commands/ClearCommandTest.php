<?php

namespace Spatie\MediaLibrary\Tests\Conversions\Commands;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class ClearCommandTest extends TestCase
{
    protected array $media;

    public function setUp(): void
    {
        parent::setUp();

        $this->media['model1']['collection1'] = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('collection1');

        $this->media['model1']['collection2'] = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('collection2');

        $this->media['model2']['collection1'] = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('collection1');

        $this->media['model2']['collection2'] = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('collection2');

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }

    /** @test */
    public function it_can_clear_all_media()
    {
        $this->artisan('media-library:clear');

        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }

    /** @test */
    public function it_can_clear_media_from_a_specific_model_type()
    {
        $this->artisan('media-library:clear', [
            'modelType' => TestModel::class,
        ]);

        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }

    /** @test */
    public function it_can_clear_media_from_a_specific_collection()
    {
        $this->artisan('media-library:clear', [
            'collectionName' => 'collection2',
        ]);

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }

    /** @test */
    public function it_can_clear_media_from_a_specific_model_type_and_collection()
    {
        $this->artisan('media-library:clear', [
            'modelType' => TestModel::class,
            'collectionName' => 'collection2',
        ]);

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }
}
