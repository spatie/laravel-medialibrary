<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Commands\ClearCommand;
use Spatie\MediaLibrary\Commands\RegenerateCommand;

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
        $mediaClass = config('laravel-medialibrary.media_model');
        $mediaClass::observe(new MediaObserver());

        $this->publishes([
            __DIR__.'/../resources/config/laravel-medialibrary.php' => config_path('laravel-medialibrary.php'),
        ], 'config');

        if (!class_exists('CreateMediaTable')) {

            // Publish the migration
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../resources/migrations/create_media_table.php.stub' => database_path('migrations/'.$timestamp.'_create_media_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/laravel-medialibrary.php', 'laravel-medialibrary');

        $this->app->singleton(MediaRepository::class);

        $this->app->bind('command.medialibrary:regenerate', RegenerateCommand::class);
        $this->app->bind('command.medialibrary:clear', ClearCommand::class);

        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
        ]);
    }
}
