<?php

namespace Spatie\MediaLibrary\Conversions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PerformConversionsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    public $deleteWhenMissingModels = true;

    public function __construct(protected ConversionCollection $conversions, protected Media $media, protected bool $onlyMissing = false)
    {
    }

    public function handle(FileManipulator $fileManipulator): bool
    {
        $fileManipulator->performConversions($this->conversions, $this->media, $this->onlyMissing);

        return true;
    }
}
