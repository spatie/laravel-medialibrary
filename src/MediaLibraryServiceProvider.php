<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Commands\CleanCommand;
use Spatie\MediaLibrary\Commands\ClearCommand;
use Laravel\Lumen\Application as LumenApplication;
use Spatie\MediaLibrary\Commands\RegenerateCommand;
use Illuminate\Foundation\Application as LaravelApplication;

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
        if ($this->app instanceof LaravelApplication) {
            $this->publishes([
                __DIR__.'/../config/laravel-medialibrary.php' => config_path('laravel-medialibrary.php'),
            ], 'config');

            if (! class_exists('CreateMediaTable')) {
                // Publish the migration
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../database/migrations/create_media_table.php.stub' => database_path('migrations/'.$timestamp.'_create_media_table.php'),
                  ], 'migrations');
            }
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('laravel-medialibrary');
        }

        $mediaClass = config('laravel-medialibrary.media_model');
        $mediaClass::observe(new MediaObserver());
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-medialibrary.php', 'laravel-medialibrary');

        $this->app->singleton(MediaRepository::class, function ($app) {
            // set class explicitly, otherwise in case of a custom class with custom table name commands will fail
            $mediaClass = $this->app['config']['laravel-medialibrary']['media_model'];

            return new MediaRepository(new $mediaClass);
        });

        $this->app->bind('command.medialibrary:regenerate', RegenerateCommand::class);
        $this->app->bind('command.medialibrary:clear', ClearCommand::class);
        $this->app->bind('command.medialibrary:clean', CleanCommand::class);

        $this->app->bind(FilesystemInterface::class, Filesystem::class);

        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
            'command.medialibrary:clean',
        ]);
    }
}
