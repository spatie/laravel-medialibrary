<?php

namespace Spatie\MediaLibrary\Tests\Feature\FileAdder\MediaConversions;

use Spatie\MediaLibrary\File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;

class MediaCollectionTest extends TestCase
{
    /** @test */
    public function it_will_use_the_disk_from_a_media_collection()
    {
        $testModel = new class extends TestModelWithConversion {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')
                    ->useDisk('secondMediaDisk');
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $this->assertFileNotExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('other-images');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');
    }

    /** @test */
    public function it_will_not_use_the_disk_name_of_the_collection_if_a_diskname_is_specified_while_adding()
    {
        $testModel = new class extends TestModelWithConversion {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')
                    ->useDisk('secondMediaDisk');
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/test.jpg');

        $this->assertFileNotExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    }

    /** @test */
    public function it_can_register_media_conversions_when_defining_media_collections()
    {
        $testModel = new class extends TestModelWithoutMediaConversions {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function (Media $media) {
                        $this
                            ->addMediaConversion('thumb')
                            ->greyscale();
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
    }

    /** @test */
    public function it_will_not_use_media_conversions_from_an_unrelated_collection()
    {
        $testModel = new class extends TestModelWithoutMediaConversions {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function (Media $media) {
                        $this
                            ->addMediaConversion('thumb')
                            ->greyscale();
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('unrelated-collection');

        $this->assertFileNotExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');
    }

    /** @test */
    public function it_will_use_conversions_defined_in_conversions_and_conversions_defined_in_collections()
    {
        $testModel = new class extends TestModelWithoutMediaConversions {
            public function registerMediaConversions(Media $media = null)
            {
                $this
                    ->addMediaConversion('another-thumb')
                    ->greyscale();
            }

            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->registerMediaConversions(function (Media $media = null) {
                        $this
                            ->addMediaConversion('thumb')
                            ->greyscale();
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $media = $model->addMedia($this->getTestJpg())->toMediaCollection('images', 'public');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-thumb.jpg');

        $this->assertFileExists($this->getTempDirectory('media').'/'.$media->id.'/conversions/test-another-thumb.jpg');
    }

    /** @test */
    public function it_can_accept_certain_files()
    {
        $testModel = new class extends TestModelWithConversion {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->acceptsFile(function (File $file) {
                        return $file->mimeType === 'image/jpeg';
                    });
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $this->expectException(FileUnacceptableForCollection::class);

        $model->addMedia($this->getTestPdf())->preservingOriginal()->toMediaCollection('images');
    }

    /** @test */
    public function if_the_single_file_method_is_specified_it_will_delete_all_other_media_and_will_only_keep_the_new_one()
    {
        $testModel = new class extends TestModelWithConversion {
            public function registerMediaCollections()
            {
                $this
                    ->addMediaCollection('images')
                    ->singleFile();
            }
        };

        $model = $testModel::create(['name' => 'testmodel']);

        $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

        $this->assertCount(1, $model->getMedia('images'));
    }
}
