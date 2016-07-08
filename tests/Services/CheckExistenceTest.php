<?php

namespace Spatie\Test\Services;

use Spatie\MediaLibrary\Media;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\Test\SecondTestModel;
use Spatie\MediaLibrary\Services\CheckExistence as Service;

class CheckExistenceTest extends TestCase
{
    /** @var  Media $model */
    private $mediaModel;

    /** @var MediaRepository $repository */
    private $repository;

    /** @var  Service $service */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $firstModel = TestModel::find(1);
        $firstModel->addMedia(__DIR__ . '/../temp/testfiles/test.jpg')->toMediaLibrary();
        $secondModel = SecondTestModel::create(['name' => 'test']);;
        $secondModel->addMedia(__DIR__ . '/../temp/testfiles/second-test.jpg')->toMediaLibrary();

        $this->mediaModel = new Media();
        $this->repository = new MediaRepository($this->mediaModel);
        $this->service = new Service($this->repository);
    }

    /** @test */
    public function it_yields_total_first_then_one()
    {
        $generator = $this->service->handle();
        $count = 0;

        foreach ($generator as $item) {
            if ($count === 0) {
                $this->assertEquals(2, $item);
                $count++;
                continue;
            }
            $this->assertEquals(1, $item);
        }
    }

    /** @test */
    public function it_returns_a_collection_of_media_from_getReturn()
    {
        unlink(__DIR__ . '/../temp/media/2/second-test.jpg');
        $value = $this->service->handleAndReturn();
        $this->assertEquals(Media::class, get_class($value->first()));
        $this->assertEquals(Collection::class, get_class($value));
    }

    /** @test */
    public function it_detects_a_missing_media_file()
    {
        unlink(__DIR__ . '/../temp/media/2/second-test.jpg');
        $value = $this->service->handleAndReturn();
        $this->assertEquals(1, $value->count());
    }

    /** @test */
    public function it_properly_searches_inclusively()
    {
        $value = $this->service->handle('only', collect([TestModel::class]));
        $count = 0;
        foreach($value as $thing) {
            $count++;
        }
        $this->assertEquals(2, $count);
    }

    /** @test */
    public function it_properly_serches_exclusively()
    {
        $value = $this->service->handle('except', collect([TestModel::class]));
        $count = 0;
        foreach($value as $thing) {
            $count++;
        }
        $this->assertEquals(2, $count);
    }
}
