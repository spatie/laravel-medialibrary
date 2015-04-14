<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/config/laravel-medialibrary.php' => config_path('laravel-medialibrary'),
            'config'
        ]);

        // Publish the migration
        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__ . '/migrations/create_media_table.php' => base_path('/database/migrations/'.$timestamp.'_create_media_table.php'),
            'migrations'
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
    }
}