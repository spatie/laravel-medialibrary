<?php

namespace Spatie\MediaLibrary\Tests;

use Carbon\Carbon;
use Dotenv\Dotenv;
use File;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversionQueued;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversionsOnOtherDisk;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithMorphMap;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithResponsiveImages;
use ZipArchive;

abstract class TestCase extends Orchestra
{
    protected TestModel $testModel;

    protected TestModel $testUnsavedModel;

    protected TestModelWithConversion $testModelWithConversion;

    protected TestModelWithoutMediaConversions $testModelWithoutMediaConversions;

    protected TestModelWithConversionQueued $testModelWithConversionQueued;

    protected TestModelWithMorphMap $testModelWithMorphMap;

    protected TestModelWithResponsiveImages $testModelWithResponsiveImages;

    protected TestModelWithConversionsOnOtherDisk $testModelWithConversionsOnOtherDisk;

    public function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->setUpTempTestFiles();

        $this->testModel = TestModel::first();
        $this->testUnsavedModel = new TestModel();
        $this->testModelWithConversion = TestModelWithConversion::first();
        $this->testModelWithConversionQueued = TestModelWithConversionQueued::first();
        $this->testModelWithoutMediaConversions = TestModelWithoutMediaConversions::first();
        $this->testModelWithMorphMap = TestModelWithMorphMap::first();
        $this->testModelWithResponsiveImages = TestModelWithResponsiveImages::first();
        $this->testModelWithConversionsOnOtherDisk = TestModelWithConversionsOnOtherDisk::first();
    }

    protected function loadEnvironmentVariables()
    {
        if (! file_exists(__DIR__.'/../.env')) {
            return;
        }

        $dotenv = Dotenv::create(__DIR__.'/..');

        $dotenv->load();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MediaLibraryServiceProvider::class,
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
            'url' => '/media'
        ]);

        $app['config']->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
            'url' => '/media2'
        ]);

        $app->bind('path.public', fn() => $this->getTempDirectory());

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        $this->setupS3($app);
        $this->setUpMorphMap();

        $app['config']->set('view.paths', [__DIR__.'/Support/resources/views']);
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
        File::copyDirectory(__DIR__.'/Support/testfiles', $this->getTestFilesDirectory());
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
        return __DIR__.'/Support/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getMediaDirectory($suffix = '')
    {
        return $this->getTempDirectory().'/media'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestFilesDirectory($suffix = '')
    {
        return $this->getTempDirectory().'/testfiles'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestJpg()
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getSmallTestJpg()
    {
        return $this->getTestFilesDirectory('smallTest.jpg');
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

    public function getTestWebp()
    {
        return $this->getTestFilesDirectory('test.webp');
    }

    public function getTestMp4()
    {
        return $this->getTestFilesDirectory('test.mp4');
    }

    public function getTestImageWithoutExtension()
    {
        return $this->getTestFilesDirectory('image');
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
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            'region' => getenv('AWS_DEFAULT_REGION'),
            'bucket' => getenv('AWS_BUCKET'),
        ];

        $app['config']->set('filesystems.disks.s3_disk', $s3Configuration);
    }

    public function renderView($view, $parameters)
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }

    protected function setNow($year, int $month = 1, int $day = 1)
    {
        $newNow = $year instanceof Carbon
            ? $year
            : Carbon::createFromDate($year, $month, $day);

        Carbon::setTestNow($newNow);
    }

    protected function progressTime(int $minutes)
    {
        $newNow = now()->copy()->addMinutes($minutes);

        Carbon::setTestNow($newNow);

        return $this;
    }

    protected function assertFileExistsInZip($zipPath, $filename)
    {
        $this->assertTrue($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} contains a file name {$filename}");
    }

    protected function assertFileDoesntExistsInZip($zipPath, $filename)
    {
        $this->assertFalse($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} doesn't contain a file name {$filename}");
    }

    protected function fileExistsInZip($zipPath, $filename): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) === true) {
            return $zip->locateName($filename, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }
}
