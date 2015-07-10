<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Commands\RegenerateCommand;
use Spatie\MediaLibrary\UrlGenerator\UrlGenerator;

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
            __DIR__.'/../resources/config/laravel-medialibrary.php' => $this->app->configPath().'/'.'laravel-medialibrary.php',
        ], 'config');

        if (!class_exists('CreateMediaTable')) {

            // Publish the migration
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../resources/migrations/create_media_table.php.stub' => $this->app->basePath().'/'.'database/migrations/'.$timestamp.'_create_media_table.php',
            ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/laravel-medialibrary.php', 'laravel-medialibrary');

        $this->app->bind(Filesystem::class, function (Application $app) {
            return new Filesystem($this->app->filesystem->disk($app->config->get('laravel-medialibrary.filesystem')), $app->config);
        });

        $this->app->bind(UrlGenerator::class, function (Application $app) {
            $urlGeneratorClass = 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($this->getDriverType()).'UrlGenerator';

            $customClass = $app->config->get('laravel-medialibrary.custom_url_generator_class');

            if ($customClass != '' && class_exists($customClass) && $customClass instanceof UrlGenerator) {
                $urlGeneratorClass = $customClass;
            }

            return $this->app->make($urlGeneratorClass);
        });

        $this->app->singleton(MediaRepository::class);

        $this->app['command.medialibrary:regenerate'] = $this->app->make(RegenerateCommand::class);

        $this->commands(['command.medialibrary:regenerate']);
    }

    /**
     * @return string
     */
    public function getDriverType()
    {
        $filesystem = $this->app->config->get('laravel-medialibrary.filesystem');

        return $this->app->config->get('filesystems.disks.'.$filesystem.'.driver');
    }
}
