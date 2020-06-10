<?php

namespace Spatie\MediaLibrary\Tests\Conversions\Commands;

use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

class CleanConversionsTest extends TestCase
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

        $this->artisan('media-library:clean');

        $this->assertFileDoesNotExist($deprecatedImage);
        $this->assertFileExists($this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg"));
    }

    /** @test */
    public function generated_conversion_are_cleared_after_cleanup()
    {
        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $this->media['model2']['collection1'];

        Media::where('id', '<>', $media->id)->delete();

        $media->markAsConversionGenerated('test-deprecated', true);

        $media->save();

        $this->assertTrue($media->refresh()->hasGeneratedConversion('test-deprecated'));

        $deprecatedImage = $this->getMediaDirectory("{$media->id}/conversions/test-deprecated.jpg");

        touch($deprecatedImage);

        $this->artisan('media-library:clean');

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

        $this->artisan('media-library:clean', [
            'modelType' => TestModelWithConversion::class,
        ]);

        $this->assertFileExists($deprecatedImage1);
        $this->assertFileDoesNotExist($deprecatedImage2);
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

        $this->artisan('media-library:clean', [
            'collectionName' => 'collection2',
        ]);

        $this->assertFileExists($deprecatedImage1);
        $this->assertFileDoesNotExist($deprecatedImage2);
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

        $this->artisan('media-library:clean', [
            'modelType' => TestModel::class,
            'collectionName' => 'collection1',
        ]);

        $this->assertFileDoesNotExist($deprecatedImage1);
        $this->assertFileExists($deprecatedImage2);
        $this->assertFileExists($deprecatedImage3);
    }

    /** @test */
    public function it_can_clean_orphan_files_in_the_media_disk()
    {
        // Dirty delete
        DB::table('media')->delete($this->media['model1']['collection1']->id);

        $this->artisan('media-library:clean');

        $this->assertFileDoesNotExist($this->getMediaDirectory($this->media['model1']['collection1']->id));
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

        $this->artisan('media-library:clean');

        $media->refresh();

        $this->assertEquals($originalResponsiveImagesContent, $media->responsive_images);
        $this->assertFileDoesNotExist($deprecatedReponsiveImagesPath);
    }
}
