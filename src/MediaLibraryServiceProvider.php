<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\UrlGenerator\UrlGenerator;
use Storage;

class MediaLibraryServiceProvider extends ServiceProvider
{
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
        $this->publishes([
            __DIR__.'/../resources/config/laravel-medialibrary.php' => config_path('laravel-medialibrary.php'),
        ], 'config');

        if (!class_exists('CreateMediaTable')) {

            // Publish the migration
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../resources/migrations/create_media_table.php' => base_path('database/migrations/'.$timestamp.'_create_media_table.php'),

            ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/laravel-medialibrary.php', 'laravel-medialibrary');

        $this->app->bind(FileSystem::class, function (Application $app) {
            return new FileSystem(Storage::disk($app->config->get('laravel-medialibrary.filesystem')), $app->config);
        });

        $this->app->bind(UrlGenerator::class, function (Application $app) {
            $urlGeneratorClass = 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($this->getDriverType()).'UrlGenerator';

            $customClass = $app->config->get('laravel-medialibrary.custom_url_generator_class');

            if ($customClass != '' && class_exists($customClass) && $customClass instanceof UrlGenerator) {
                $urlGeneratorClass = $customClass;
            }

            return new $urlGeneratorClass();
        });

        $this->app['command.medialibrary:regenerate'] = app(RegenerateCommand::class);

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

    public function getDriverType()
    {
        return $this->app->config->get('filesystems.disks.'.$this->app->config->get('laravel-medialibrary.filesystem').'.driver');
    }
}
