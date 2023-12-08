<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video as FFMpegVideo;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;

class Video extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
        ]);

        $video = $ffmpeg->open($file);

        if (! ($video instanceof FFMpegVideo)) {
            return null;
        }

        $duration = $ffmpeg->getFFProbe()->format($file)->get('duration');

        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;
        $seconds = $duration <= $seconds ? 0 : $seconds;

        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $frame = $video->frame(TimeCode::fromSeconds($seconds));
        $frame->save($imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return class_exists('\\FFMpeg\\FFMpeg');
    }

    public function supportedExtensions(): Collection
    {
        return collect(['webm', 'mov', 'mp4']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['video/webm', 'video/mpeg', 'video/mp4', 'video/quicktime']);
    }
}
