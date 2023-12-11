<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\MediaLibrary\Conversions\Commands\RegenerateCommand;
use Spatie\MediaLibrary\MediaCollections\Commands\CleanCommand;
use Spatie\MediaLibrary\MediaCollections\Commands\ClearCommand;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;

class MediaLibraryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-medialibrary')
            ->hasConfigFile()
            ->hasMigration('create_media_table')
            ->hasViews()
            ->hasCommands([
                RegenerateCommand::class,
                ClearCommand::class,
                CleanCommand::class,
            ]);
    }

    public function packageBooted()
    {
        $mediaClass = config('medialibrary.media_model', Media::class);

        $mediaClass::observe(new MediaObserver());
    }

    public function packageRegistered()
    {
        $this->app->bind(WidthCalculator::class, config('medialibrary.responsive_images.width_calculator'));
        $this->app->bind(TinyPlaceholderGenerator::class, config('medialibrary.responsive_images.tiny_placeholder_generator'));

        $this->app->scoped(MediaRepository::class, function () {
            $mediaClass = config('medialibrary.media_model');

            return new MediaRepository(new $mediaClass());
        });
    }
}
