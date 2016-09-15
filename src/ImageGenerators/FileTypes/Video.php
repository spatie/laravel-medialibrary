<?php

namespace Spatie\MediaLibrary\ImageGenerator\FileTypes;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;

class Video extends BaseGenerator
{
    public function convert(string $file, Conversion $conversion = null) : string
    {
        $file = $media->getPath();

        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries' => config('laravel-medialibrary.ffmpeg_binaries'),
            'ffprobe.binaries' => config('laravel-medialibrary.ffprobe_binaries'),
        ]);
        $video = $ffmpeg->open($file);

        $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($conversion->getExtractVideoFrameAtSecond()));
        $frame->save($imageFile);

        return $imageFile;
    }

    public function areRequirementsInstalled() : bool
    {
        return class_exists('\\FFMpeg\\FFMpeg');
    }

    public function supportedExtensions() : Collection
    {
        return collect(['webm', 'mov', 'mp4']);
    }

    public function supportedMimeTypes() : Collection
    {
        return collect(['video/webm', 'video/mpeg', 'video/mp4', 'video/quicktime']);
    }

    public function supportedTypes() : Collection
    {
        return collect('video');
    }
}
