<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Commands\CleanCommand;
use Spatie\MediaLibrary\Commands\ClearCommand;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Commands\RegenerateCommand;
use Spatie\MediaLibrary\Filesystem\DefaultFilesystem;

class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'config');

        if (! class_exists('CreateMediaTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_media_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_media_table.php'),
            ], 'migrations');
        }

        $mediaClass = config('medialibrary.media_model');

        $mediaClass::observe(new MediaObserver());
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/medialibrary.php', 'medialibrary');

        $this->app->singleton(MediaRepository::class, function () {
            $mediaClass = $this->app['config']['medialibrary']['media_model'];

            return new MediaRepository(new $mediaClass);
        });

        $this->app->bind('command.medialibrary:regenerate', RegenerateCommand::class);
        $this->app->bind('command.medialibrary:clear', ClearCommand::class);
        $this->app->bind('command.medialibrary:clean', CleanCommand::class);

        $this->app->bind(Filesystem::class, DefaultFilesystem::class);

        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
            'command.medialibrary:clean',
        ]);
    }
}
