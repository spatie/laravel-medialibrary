<?php

namespace Spatie\Medialibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\Medialibrary\Commands\CleanCommand;
use Spatie\Medialibrary\Commands\ClearCommand;
use Spatie\Medialibrary\Commands\RegenerateCommand;
use Spatie\Medialibrary\Filesystem\Filesystem;
use Spatie\Medialibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\Medialibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class MedialibraryServiceProvider extends ServiceProvider
{
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

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/medialibrary'),
        ], 'views');

        $mediaClass = config('medialibrary.media_model');

        $mediaClass::observe(new MediaObserver());

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'medialibrary');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/medialibrary.php', 'medialibrary');

        $this->app->singleton(MediaRepository::class, function () {
            $mediaClass = config('medialibrary.media_model');

            return new MediaRepository(new $mediaClass);
        });

        $this->app->bind('command.medialibrary:regenerate', RegenerateCommand::class);
        $this->app->bind('command.medialibrary:clear', ClearCommand::class);
        $this->app->bind('command.medialibrary:clean', CleanCommand::class);

        $this->app->bind(Filesystem::class, Filesystem::class);

        $this->app->bind(WidthCalculator::class, config('medialibrary.responsive_images.width_calculator'));
        $this->app->bind(TinyPlaceholderGenerator::class, config('medialibrary.responsive_images.tiny_placeholder_generator'));

        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
            'command.medialibrary:clean',
        ]);

        $this->registerDeprecatedConfig();
    }

    protected function registerDeprecatedConfig()
    {
        if (! config('medialibrary.disk_name')) {
            config(['medialibrary.disk_name' => config('medialibrary.default_filesystem')]);
        }
    }
}
