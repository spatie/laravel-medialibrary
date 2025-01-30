<?php

namespace Programic\MediaLibrary\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\ResponsiveImages\ResponsiveImageGenerator;

class GenerateResponsiveImagesJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected Media $media) {}

    public function handle(): bool
    {
        /** @var ResponsiveImageGenerator $responsiveImageGenerator */
        $responsiveImageGenerator = app(ResponsiveImageGenerator::class);

        $responsiveImageGenerator->generateResponsiveImages($this->media);

        return true;
    }
}
