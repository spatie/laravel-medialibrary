<?php

namespace Spatie\MediaLibrary\Tests;

use CreateTemporaryUploadsTable;
use Dotgetenv\Dotgetenv;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Schema;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\MediaLibrary\Support\MediaLibraryPro;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionQueued;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionsOnOtherDisk;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionUsingModelInstance;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMorphMap;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMorphMapInSideModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMultipleConversions;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithoutMediaConversions;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithPreviewConversion;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithResponsiveImages;
use ZipArchive;

abstract class TestCase extends Orchestra
{
    protected TestModel $testModel;

    protected TestModel $testUnsavedModel;

    protected TestModelWithConversion $testModelWithConversion;

    protected TestModelWithMultipleConversions $testModelWithMultipleConversions;

    protected TestModelWithPreviewConversion $testModelWithPreviewConversion;

    protected TestModelWithoutMediaConversions $testModelWithoutMediaConversions;

    protected TestModelWithConversionQueued $testModelWithConversionQueued;

    protected TestModelWithMorphMap $testModelWithMorphMap;

    protected TestModelWithMorphMapInSideModel $testModelWithMorphMapInSideModel;

    protected TestModelWithResponsiveImages $testModelWithResponsiveImages;

    protected TestModelWithConversionsOnOtherDisk $testModelWithConversionsOnOtherDisk;

    protected TestModelWithConversionUsingModelInstance $testModelWithConversionUsingModelInstance;

    protected function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->setUpTempTestFiles();

        $this->testModel = TestModel::first();
        $this->testUnsavedModel = new TestModel;
        $this->testModelWithConversion = TestModelWithConversion::first();
        $this->testModelWithMultipleConversions = TestModelWithMultipleConversions::first();
        $this->testModelWithPreviewConversion = TestModelWithPreviewConversion::first();
        $this->testModelWithConversionQueued = TestModelWithConversionQueued::first();
        $this->testModelWithoutMediaConversions = TestModelWithoutMediaConversions::first();
        $this->testModelWithMorphMap = TestModelWithMorphMap::first();
        $this->testModelWithMorphMapInSideModel = TestModelWithMorphMapInSideModel::first();
        $this->testModelWithResponsiveImages = TestModelWithResponsiveImages::first();
        $this->testModelWithConversionsOnOtherDisk = TestModelWithConversionsOnOtherDisk::first();
        $this->testModelWithConversionUsingModelInstance = TestModelWithConversionUsingModelInstance::first();
    }

    protected function loadEnvironmentVariables()
    {
        if (! file_exists(__DIR__.'/../.getenv')) {
            return;
        }

        $dotEnv = Dotgetenv::createImmutable(__DIR__.'/..');

        $dotEnv->load();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        $serviceProviders = [
            MediaLibraryServiceProvider::class,
        ];

        return $serviceProviders;
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        $this->initializeDirectory($this->getTempDirectory());

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => $this->getMediaDirectory(),
            'url' => '/media',
        ]);

        config()->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
            'url' => '/media2',
        ]);

        $app->bind('path.public', fn () => $this->getTempDirectory());

        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        $this->setupS3($app);
        $this->setUpMorphMap();

        config()->set('view.paths', [__DIR__.'/TestSupport/resources/views']);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
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

        if (MediaLibraryPro::isInstalled()) {
            include_once __DIR__.'/../vendor/spatie/laravel-medialibrary-pro/database/migrations/create_temporary_uploads_table.stub';
            (new CreateTemporaryUploadsTable)->up();
        }

        $mediaTableMigration = require __DIR__.'/../database/migrations/create_media_table.php.stub';

        $mediaTableMigration->up();
    }

    protected function setUpDatabaseCustomKeyName()
    {
        $customKeyNameMigration = new class extends Migration
        {
            public function up()
            {
                Schema::table('media', function (Blueprint $table) {
                    $table->renameColumn('id', 'custom_key_id');
                });
            }
        };

        $customKeyNameMigration->up();
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__.'/TestSupport/testfiles', $this->getTestFilesDirectory());
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    public function getTestsPath($suffix = ''): string
    {
        if ($suffix !== '') {
            $suffix = "/{$suffix}";
        }

        return __DIR__.$suffix;
    }

    public function getTempDirectory(string $suffix = ''): string
    {
        return __DIR__.'/TestSupport/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getMediaDirectory(string $suffix = ''): string
    {
        return $this->getTempDirectory().'/media'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestFilesDirectory(string $suffix = ''): string
    {
        return $this->getTempDirectory().'/testfiles'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getTestJpg(): string
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getSmallTestJpg(): string
    {
        return $this->getTestFilesDirectory('smallTest.jpg');
    }

    public function getTestPng(): string
    {
        return $this->getTestFilesDirectory('test.png');
    }

    public function getUppercaseExtensionTestPng(): string
    {
        return $this->getTestFilesDirectory('uppercaseExtensionTest.PNG');
    }

    public function getTestTiff(): string
    {
        return $this->getTestFilesDirectory('test.tiff');
    }

    public function getTestWebm(): string
    {
        return $this->getTestFilesDirectory('test.webm');
    }

    public function getTestPdf(): string
    {
        return $this->getTestFilesDirectory('test.pdf');
    }

    public function getTestSvg(): string
    {
        return $this->getTestFilesDirectory('test.svg');
    }

    public function getTestWebp(): string
    {
        return $this->getTestFilesDirectory('test.webp');
    }

    public function getTestAvif(): string
    {
        return $this->getTestFilesDirectory('test.avif');
    }

    public function getTestHeic(): string
    {
        return $this->getTestFilesDirectory('test.heic');
    }

    public function getTestMp4(): string
    {
        return $this->getTestFilesDirectory('test.mp4');
    }

    public function getTestImageWithoutExtension(): string
    {
        return $this->getTestFilesDirectory('image');
    }

    public function getTestImageEndingWithUnderscore(): string
    {
        return $this->getTestFilesDirectory('test_.jpg');
    }

    public function getAntaresThumbJpgWithAccent(): string
    {
        return $this->getTestFilesDirectory('antarèsthumb.jpg');
    }

    private function setUpMorphMap(): void
    {
        Relation::morphMap([
            'test-model-with-morph-map' => TestModelWithMorphMap::class,
        ]);
    }

    private function setupS3($app): void
    {
        config()->set('filesystems.disks.s3_disk', [
            'driver' => 's3',
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            'region' => getenv('AWS_DEFAULT_REGION'),
            'bucket' => getenv('AWS_BUCKET'),
        ]);
    }

    public function renderView($view, $parameters): string
    {
        $this->artisan('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }

    protected function assertFileExistsInZip(string $zipPath, string $filename): void
    {
        $this->assertTrue(
            $this->fileExistsInZip($zipPath, $filename),
            "Failed to assert that {$zipPath} contains a file name {$filename}"
        );
    }

    protected function assertFileExistsInZipRecognizeFolder(string $zipPath, string $filename): void
    {
        $this->assertTrue(
            $this->fileExistsInZipRecognizeFolder($zipPath, $filename),
            "Failed to assert that {$zipPath} contains a file name {$filename} by recognizing folders"
        );
    }

    protected function assertFileDoesntExistsInZip(string $zipPath, string $filename): void
    {
        $this->assertFalse(
            $this->fileExistsInZip($zipPath, $filename),
            "Failed to assert that {$zipPath} doesn't contain a file name {$filename}"
        );
    }

    protected function fileExistsInZip($zipPath, $filename): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === true) {
            return $zip->locateName($filename, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }

    protected function fileExistsInZipRecognizeFolder($zipPath, $filename): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === true) {
            return $zip->locateName($filename) !== false;
        }

        return false;
    }

    protected function makePathOsSafe(string $path): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return str_replace('/', '\\', $path);
        }

        return $path;
    }
}
