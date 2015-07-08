<?php

namespace Spatie\MediaLibrary\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Media;

class PerformConversions extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var \Spatie\MediaLibrary\Conversion\ConversionCollection
     */
    protected $conversions;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $media;

    public function __construct(ConversionCollection $conversions, Media $media)
    {
        $this->conversions = $conversions;
        $this->media = $media;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        app(FileManipulator::class)->performConversions($this->conversions, $this->media);

        return true;
    }
}
