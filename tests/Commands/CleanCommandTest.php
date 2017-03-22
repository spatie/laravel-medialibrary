<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use DB;
use Spatie\MediaLibrary\Test\TestCase;
use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Test\TestModel;
use Spatie\MediaLibrary\Test\TestModelWithConversion;

class CleanCommandTest extends TestCase
{
    /** @var array */
    protected $media;

    public function setUp()
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

        mkdir($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/conversions"));
        mkdir($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/conversions"));

        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
    }

    /** @test */
    public function it_can_clean_deprecated_conversion_files()
    {
        $media = $this->media['model2']['collection1'];
        $deprecatedImage = $this->getMediaDirectory("{$media->id}/conversions/deprecated.jpg");

        touch($deprecatedImage);
        $this->assertFileExists($deprecatedImage);

        Artisan::call('medialibrary:clean');

        $this->assertFileNotExists($deprecatedImage);
        $this->assertFileExists($this->getMediaDirectory("{$media->id}/conversions/thumb.jpg"));
    }

    /** @test */
    public function it_can_clean_deprecated_conversion_files_from_a_specific_model_type()
    {
        $media1 = $this->media['model1']['collection1'];
        $media2 = $this->media['model2']['collection1'];

        $deprecatedImage1 = $this->getMediaDirectory("{$media1->id}/conversions/deprecated.jpg");
        $deprecatedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/deprecated.jpg");
        touch($deprecatedImage1);
        touch($deprecatedImage2);

        Artisan::call('medialibrary:clean', [
            'modelType' => TestModelWithConversion::class,
        ]);

        $this->assertFileExists($deprecatedImage1);
        $this->assertFileNotExists($deprecatedImage2);
    }

    /** @test */
    public function it_can_clean_deprecated_conversion_files_from_a_specific_collection()
    {
        $media1 = $this->media['model1']['collection1'];
        $media2 = $this->media['model1']['collection2'];

        $deprecatedImage1 = $this->getMediaDirectory("{$media1->id}/conversions/deprecated.jpg");
        $deprecatedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/deprecated.jpg");
        touch($deprecatedImage1);
        touch($deprecatedImage2);

        Artisan::call('medialibrary:clean', [
            'collectionName' => 'collection2',
        ]);

        $this->assertFileExists($deprecatedImage1);
        $this->assertFileNotExists($deprecatedImage2);
    }

    /** @test */
    public function it_can_clean_deprecated_conversion_files_from_a_specific_model_type_and_collection()
    {
        $media1 = $this->media['model1']['collection1'];
        $media2 = $this->media['model1']['collection2'];
        $media3 = $this->media['model2']['collection1'];

        $deprecatedImage1 = $this->getMediaDirectory("{$media1->id}/conversions/deprecated.jpg");
        $deprecatedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/deprecated.jpg");
        $deprecatedImage3 = $this->getMediaDirectory("{$media3->id}/conversions/deprecated.jpg");

        touch($deprecatedImage1);
        touch($deprecatedImage2);
        touch($deprecatedImage3);

        Artisan::call('medialibrary:clean', [
            'modelType' => TestModel::class,
            'collectionName' => 'collection1',
        ]);

        $this->assertFileNotExists($deprecatedImage1);
        $this->assertFileExists($deprecatedImage2);
        $this->assertFileExists($deprecatedImage3);
    }

    /** @test */
    public function it_can_clean_orphan_files_in_the_media_disk()
    {
        // Dirty delete
        DB::table('media')->delete($this->media['model1']['collection1']->id);

        Artisan::call('medialibrary:clean');

        $this->assertFileNotExists($this->getMediaDirectory($this->media['model1']['collection1']->id));
        $this->assertFileExists($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));
    }
}
