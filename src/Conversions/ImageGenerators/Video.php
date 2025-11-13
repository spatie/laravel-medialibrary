<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video as FFMpegVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\Conversions\Conversion;

class Video extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        // Create an FFMpeg instance
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
            'timeout' => config('media-library.ffmpeg_timeout', 900),
            'ffmpeg.threads' => config('media-library.ffmpeg_threads', 0),
        ]);

        $video = $ffmpeg->open($file);

        if (! ($video instanceof FFMpegVideo)) {
            return null;
        }

        // Get the duration of the video in seconds
        $duration = (float) $ffmpeg->getFFProbe()->format($file)->get('duration');

        // Determine at which second to extract the frame
        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;

        // Clamp the seconds to be within the video duration
        $seconds = Number::clamp($seconds, 0, $duration);

        // Define the output image file path
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        // Extract the frame at the specified time and save it as an image
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
        return collect(['webm', 'mov', 'mp4', 'm4v']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['video/webm', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/x-m4v']);
    }
}
