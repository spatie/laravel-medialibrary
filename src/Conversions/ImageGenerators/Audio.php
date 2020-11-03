<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use FFMpeg\FFMpeg;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\ImageFactory;

class Audio extends ImageGenerator
{
    private const IMAGE_WIDTH = 1024;
    private const IMAGE_HEIGHT = 1024;

    public function convert(string $file, Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.png';

        $ffmpeg = FFMpeg::create(
            [
                'ffmpeg.binaries'  => config('media-library.ffmpeg_path'),
                'ffprobe.binaries' => config('media-library.ffprobe_path'),
            ]
        );

        $audio = $ffmpeg->open($file);
        //This generates a waveform image drawn with the foreground colour on a transparent background
        $waveform = $audio->waveform(
            self::IMAGE_WIDTH,
            self::IMAGE_HEIGHT,
            [config('media-library.audio_foreground')]
        );
        $waveform->save($imageFile);

        //Read the file back in again so we can fill in the background colour
        $image = ImageFactory::load($imageFile);
        //This function wants a hex colour without a # prefix, will also work with HTML named colours like 'pink'
        $image->background(str_replace('#', '', config('media-library.audio_background')));
        $image->optimize();
        $image->save($imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return class_exists('\\FFMpeg\\FFMpeg');
    }

    public function supportedExtensions(): Collection
    {
        return collect(
            [
                'aac',
                'aif',
                'aifc',
                'aiff',
                'flac',
                'm4a',
                'mp3',
                'mp4',
                'ogg',
                'wav',
                'wma',
            ]
        );
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(
            [
                'audio/aac',
                'audio/flac',
                'audio/mp4',
                'audio/mpeg3',
                'audio/ogg',
                'audio/vnd.wav',
                'audio/x-aiff',
                'audio/x-flac',
                'video/x-ms-asf',
            ]
        );
    }
}
