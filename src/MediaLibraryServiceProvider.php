<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\ImageManipulators\GlideImageManipulator;
use Spatie\MediaLibrary\FileSystems\LocalFileSystem;
use Spatie\MediaLibrary\FileSystems\FileSystemInterface;
use Spatie\MediaLibrary\ImageManipulators\ImageManipulatorInterface;
use Spatie\MediaLibrary\Repositories\MediaLibraryRepository;

class MediaLibraryServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/ToPublish/config/laravel-medialibrary.php' => config_path('laravel-medialibrary.php')
        ], 'config');

        // Publish the migration
        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__ . '/ToPublish/migrations/create_media_table.php' => base_path('database/migrations/'.$timestamp.'_create_media_table.php')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('mediaLibrary', MediaLibraryRepository::class);
        $this->app->bind(FileSystemInterface::class, LocalFileSystem::class);
        $this->app->bind(ImageManipulatorInterface::class, GlideImageManipulator::class);

        $this->app['command.medialibrary:regenerate'] = $this->app->share(
            function ($app) {
                return new Commands\RegenerateCommand();
            }
        );

        $this->commands(['command.medialibrary:regenerate']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.medialibrary:regenerate',
        ];
    }
}
