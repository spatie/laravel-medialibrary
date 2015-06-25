<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;

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
            __DIR__.'/../resources/config/config.php' => config_path('laravel-medialibrary.php'),
        ], 'config');

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../resources/config/config.php' => base_path('database/migrations/'.$timestamp.'_create_media_table.php'),
        ], 'migrations');

    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['command.medialibrary:regenerate'] = $this->app->share(
            function () {
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
