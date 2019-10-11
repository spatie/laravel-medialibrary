<?php

namespace Spatie\MediaLibrary\Tests;

use File;
use Carbon\Carbon;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithMorphMap;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithResponsiveImages;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModel */
    protected $testModel;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModel */
    protected $testUnsavedModel;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversion */
    protected $testModelWithConversion;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithoutMediaConversions */
    protected $testModelWithoutMediaConversions;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithConversionQueued */
    protected $testModelWithConversionQueued;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithMorphMap */
    protected $testModelWithMorphMap;

    /** @var \Spatie\MediaLibrary\Tests\Support\TestModels\TestModelWithResponsiveImages */
    protected $testModelWithResponsiveImages;

    public function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->setUpTempTestFiles();

        $this->testModel = TestModel::first();
        $this->testUnsavedModel = new TestModel;
        $this->testModelWithConversion = TestModelWithConversion::first();
        $this->testModelWithConversionQueued = TestModelWithConversion::first();
        $this->testModelWithoutMediaConversions = TestModelWithoutMediaConversions::first();
        $this->testModelWithMorphMap = TestModelWithMorphMap::first();
        $this->testModelWithResponsiveImages = TestModelWithResponsiveImages::first();
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
            'key' => getenv('AWS_KEY'),
            'secret' => getenv('AWS_SECRET'),
            'region' => getenv('AWS_REGION'),
            'bucket' => getenv('AWS_BUCKET'),
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
}
