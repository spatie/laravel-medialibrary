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
    use InteractsWithQueue, SerializesModels, Queueable;

    protected ConversionCollection $conversions;

    protected Media $media;

    protected bool $onlyMissing;

    public function __construct(ConversionCollection $conversions, Media $media, $onlyMissing = false)
    {
        $this->conversions = $conversions;

        $this->media = $media;

        $this->onlyMissing = $onlyMissing;
    }

    public function handle(): bool
    {
        /** @var \Spatie\MediaLibrary\Conversions\FileManipulator $fileManipulator */
        $fileManipulator = app(FileManipulator::class);

        $fileManipulator->performConversions($this->conversions, $this->media, $this->onlyMissing);

        return true;
    }
}
