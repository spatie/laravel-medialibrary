<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\MediaLibrary\Conversions\Commands\RegenerateCommand;
use Spatie\MediaLibrary\MediaCollections\Commands\CleanCommand;
use Spatie\MediaLibrary\MediaCollections\Commands\ClearCommand;
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
            ->hasConfigFile('media-library')
            ->hasMigration('create_media_table')
            ->hasViews('media-library')
            ->hasCommands([
                RegenerateCommand::class,
                ClearCommand::class,
                CleanCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $mediaClass = config('media-library.media_model', Media::class);
        $mediaObserverClass = config('media-library.media_observer', MediaObserver::class);

        $mediaClass::observe($this->app->make($mediaObserverClass));
    }

    public function packageRegistered(): void
    {
        $this->ensureAwsDefaultRegionFallback();

        $this->app->bind(WidthCalculator::class, config('media-library.responsive_images.width_calculator'));
        $this->app->bind(TinyPlaceholderGenerator::class, config('media-library.responsive_images.tiny_placeholder_generator'));

        $this->app->scoped(MediaRepository::class, function () {
            $mediaClass = config('media-library.media_model');

            return new MediaRepository(new $mediaClass);
        });
    }

    protected function ensureAwsDefaultRegionFallback(): void
    {
        $defaultRegion = env('AWS_DEFAULT_REGION');

        if (! empty($defaultRegion)) {
            return;
        }

        $region = env('AWS_REGION');

        if (empty($region)) {
            return;
        }

        // Update all S3 disk configurations to use the region from AWS_REGION
        $disks = Config::get('filesystems.disks', []);

        foreach ($disks as $diskName => $diskConfig) {
            if (isset($diskConfig['driver']) && $diskConfig['driver'] === 's3') {
                if (empty($diskConfig['region'])) {
                    Config::set("filesystems.disks.{$diskName}.region", $region);
                }
            }
        }
    }
}
