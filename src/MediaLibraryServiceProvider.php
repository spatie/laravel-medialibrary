<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Storage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        if (! class_exists('CreateMediaTable')) {
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

        $this->app->bind(FileSystem::class, function ($app) {
           return new FileSystem(Storage::disk($app->config->get('laravel-medialibrary.filesystem')), $app->config);
        });

        $this->app->bind(UrlGeneratorInterface::class, 'Spatie\MediaLibrary\UrlGenerator\\'.ucfirst($this->getDriverType()).'UrlGenerator');

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
