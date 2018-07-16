<?php

namespace Spatie\MediaLibrary\Tests\Feature\Commands;

use DB;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion;

class CleanConversionsTest extends TestCase
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
    public function it_can_clean_deprecated_conversion_files_with_none_arguments_given()
    {
        $media = $this->media['model2']['collection1'];
        $deprecatedImage = $this->getMediaDirectory("{$media->id}/conversions/test-deprecated.jpg");

        touch($deprecatedImage);
        $this->assertFileExists($deprecatedImage);

        Artisan::call('medialibrary:clean');

        $this->assertFileNotExists($deprecatedImage);
        $this->assertFileExists($this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg"));
    }

    /** @test */
    public function generated_conversion_are_cleared_after_cleanup()
    {
        /** @var \Spatie\MediaLibrary\Models\Media $media */
        $media = $this->media['model2']['collection1'];

        Media::where('id', '<>', $media->id)->delete();

        $media->markAsConversionGenerated('test-deprecated', true);

        $media->save();

        $this->assertTrue($media->refresh()->hasGeneratedConversion('test-deprecated'));

        $deprecatedImage = $this->getMediaDirectory("{$media->id}/conversions/test-deprecated.jpg");

        touch($deprecatedImage);

        Artisan::call('medialibrary:clean');

        $media->refresh();

        $this->assertFalse($media->hasGeneratedConversion('test-deprecated'));
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

    /** @test */
    public function it_can_clean_responsive_images()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $deprecatedResponsiveImageFileName = 'test___deprecatedConversion_50_41.jpg';
        $deprecatedReponsiveImagesPath = $this->getMediaDirectory("5/responsive-images/{$deprecatedResponsiveImageFileName}");
        touch($deprecatedReponsiveImagesPath);

        $originalResponsiveImagesContent = $media->responsive_images;
        $newResponsiveImages = $originalResponsiveImagesContent;
        $newResponsiveImages['deprecatedConversion'] = $originalResponsiveImagesContent['thumb'];
        $newResponsiveImages['deprecatedConversion']['urls'][0] = $deprecatedResponsiveImageFileName;
        $media->responsive_images = $newResponsiveImages;
        $media->save();

        Artisan::call('medialibrary:clean');

        $media->refresh();

        $this->assertEquals($originalResponsiveImagesContent, $media->responsive_images);
        $this->assertFileNotExists($deprecatedReponsiveImagesPath);
    }
}
