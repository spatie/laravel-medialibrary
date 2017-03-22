<?php

namespace Spatie\MediaLibrary\Test\HasMediaConversionsTrait;

use File;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModelWithoutMediaConversions;

class DeleteMediaTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        foreach (range(1, 3) as $index) {
            $this->testModelWithoutMediaConversions
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection();

            $this->testModelWithoutMediaConversions
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection('images');
        }
    }

    /** @test */
    public function it_can_clear_a_collection()
    {
        $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
        $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('images'));

        $this->testModelWithoutMediaConversions->clearMediaCollection('images');
        $this->testModelWithoutMediaConversions = $this->testModelWithoutMediaConversions->fresh();

        $this->assertCount(3, $this->testModelWithoutMediaConversions->getMedia('default'));
        $this->assertCount(0, $this->testModelWithoutMediaConversions->getMedia('images'));
    }

    /** @test */
    public function it_provides_a_chainable_method_for_clearing_a_collection()
    {
        $result = $this->testModelWithoutMediaConversions->clearMediaCollection('images');

        $this->assertInstanceOf(TestModelWithoutMediaConversions::class, $result);
    }

   /**
    * @test
    */
   public function it_will_remove_the_files_when_clearing_a_collection()
   {
       $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

       $ids->map(function ($id) {
           $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
       });

       $this->testModelWithoutMediaConversions->clearMediaCollection('images');

       $ids->map(function ($id) {
           $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
       });
   }

    /**
     * @test
     */
    public function it_will_remove_the_files_when_deleting_a_subject()
    {
        $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModelWithoutMediaConversions->delete();

        $ids->map(function ($id) {
            $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
        });
    }

    /** @test */
    public function it_will_not_remove_the_files_when_deleting_a_subject_and_preserving_media()
    {
        $ids = $this->testModelWithoutMediaConversions->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModelWithoutMediaConversions->deletePreservingMedia();

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });
    }
}
