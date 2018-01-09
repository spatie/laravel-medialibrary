<?php

namespace Spatie\MediaLibrary\Test;

use File;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\MediaLibrary\Test\TestModel */
    protected $testModel;

    /** @var \Spatie\MediaLibrary\Test\TestModel */
    protected $testUnsavedModel;

    /** @var \Spatie\MediaLibrary\Test\TestModelWithConversion */
    protected $testModelWithConversion;

    /** @var \Spatie\MediaLibrary\Test\TestModelWithoutMediaConversions */
    protected $testModelWithoutMediaConversions;

    /** @var \Spatie\MediaLibrary\Test\TestModelWithMorphMap */
    protected $testModelWithMorphMap;

    /** @var \Spatie\MediaLibrary\Test\TestModelWithSoftDeletes */
    protected $testModelWithSoftDeletes;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->setUpTempTestFiles();

        $this->testModel = TestModel::first();
        $this->testUnsavedModel = new TestModel;
        $this->testModelWithConversion = TestModelWithConversion::first();
        $this->testModelWithoutMediaConversions = TestModelWithoutMediaConversions::first();
        $this->testModelWithMorphMap = TestModelWithMorphMap::first();
        $this->testModelWithSoftDeletes = TestModelWithSoftDeletes::first();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->initializeDirectory($this->getTempDirectory());

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => $this->getMediaDirectory(),
        ]);

        $app['config']->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
        ]);

        $app->bind('path.public', function () {
            return $this->getTempDirectory();
        });

        $mediaLibraryTemp = $this->getTempDirectory('medialibrary-temp');
        if (! File::isDirectory($mediaLibraryTemp)) {
            File::makeDirectory($mediaLibraryTemp);
        }
        $app['config']->set('medialibrary.temporary_directory_path', $mediaLibraryTemp);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        $this->setupS3($app);
        $this->setUpMorphMap();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('width')->nullable();
            $table->softDeletes();
        });

        TestModel::create(['name' => 'test']);

        include_once __DIR__.'/../database/migrations/create_media_table.php.stub';

        (new \CreateMediaTable())->up();
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__.'/testfiles', $this->getTestFilesDirectory());
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    public function getTempDirectory($suffix = '')
    {
        $tempPath = __DIR__.DIRECTORY_SEPARATOR.'temp';
        if ($realTempPath = realpath($tempPath)) {
            // path exists and real path is platform-agnostic.
            $tempPath = $realTempPath;
        }

        if (! $suffix) {
            return $tempPath;
        }

        $suffixedTempPath = $tempPath.DIRECTORY_SEPARATOR.$suffix;
        if ($realSuffixedTempPath = realpath($suffixedTempPath)) {
            // suffixed path exists and real path is platform-agnostic.
            $suffixedTempPath = $realSuffixedTempPath;
        }

        return $suffixedTempPath;
    }

    public function getMediaDirectory($suffix = '')
    {
        $mediaPath = $this->getTempDirectory('media');
        if ($realMediaPath = realpath($mediaPath)) {
            // path exists and real path is platform-agnostic.
            $mediaPath = $realMediaPath;
        }

        if (! $suffix) {
            return $mediaPath;
        }

        $suffixedMediaPath = $mediaPath.DIRECTORY_SEPARATOR.$suffix;
        if ($realSuffixedMediaPath = realpath($suffixedMediaPath)) {
            // suffixed path exists and real path is platform-agnostic.
            $suffixedMediaPath = $realSuffixedMediaPath;
        }

        return $suffixedMediaPath;
    }

    public function getTestFilesDirectory($suffix = '')
    {
        $testFilesPath = $this->getTempDirectory('testfiles');
        if ($realTestFilesPath = realpath($testFilesPath)) {
            // path exists and real path is platform-agnostic.
            $testFilesPath = $realTestFilesPath;
        }

        if (! $suffix) {
            return $testFilesPath;
        }

        $suffixedTestFilesPath = $testFilesPath.DIRECTORY_SEPARATOR.$suffix;
        if ($realSuffixedTestFilesPath = realpath($suffixedTestFilesPath)) {
            // suffixed path exists and real path is platform-agnostic.
            $suffixedTestFilesPath = $realSuffixedTestFilesPath;
        }

        return $suffixedTestFilesPath;
    }

    public function getTestJpg()
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getTestPng()
    {
        return $this->getTestFilesDirectory('test.png');
    }

    public function getTestWebm()
    {
        return $this->getTestFilesDirectory('test.webm');
    }

    public function getTestPdf()
    {
        return $this->getTestFilesDirectory('test.pdf');
    }

    public function getTestSvg()
    {
        return $this->getTestFilesDirectory('test.svg');
    }

    private function setUpMorphMap()
    {
        Relation::morphMap([
            'test-model-with-morph-map' => TestModelWithMorphMap::class,
        ]);
    }

    private function setupS3($app)
    {
        $s3Configuration = [
            'driver' => 's3',
            'key' => getenv('S3_ACCESS_KEY_ID'),
            'secret' => getenv('S3_SECRET_ACCESS_KEY'),
            'region' => getenv('S3_BUCKET_REGION'),
            'bucket' => getenv('S3_BUCKET_NAME'),
        ];

        $app['config']->set('filesystems.disks.s3_disk', $s3Configuration);
        $app['config']->set(
            'medialibrary.s3.domain',
            'https://'.$s3Configuration['bucket'].'.s3.amazonaws.com'
        );
    }

    public function skipOnTravis()
    {
        if (! empty(getenv('TRAVIS_BUILD_ID'))) {
            $this->markTestSkipped('Skipping because this test does not run properly on Travis');
        }
    }
}
