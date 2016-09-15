<?php

namespace Spatie\MediaLibrary\ImageGenerator\FileTypes;

use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;
use Spatie\MediaLibrary\Conversion\Conversion;

class Video implements ImageGenerator
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string
    {
        return 'video';
    }

    /**
     * Verify that a file is this driver media type using it's extension.
     */
    public function fileExtensionIsType(string $extension) : bool
    {
        return in_array($extension, ['webm', 'mov', 'mp4']);
    }

    /**
     * Verify that a file is this driver media type using it's mime.
     */
    public function fileMimeIsType(string $mime) : bool
    {
        return in_array($mime, ['video/webm', 'video/mpeg', 'video/mp4', 'video/quicktime']);
    }

    public function hasRequirements() : bool
    {
        return class_exists('\\FFMpeg\\FFMpeg');
    }

    /**
     * Receive a file of any video type and return a thumbnail in jpg.
     */
    public function convertToImage(string $file, Conversion $conversion) : string
    {
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
}
