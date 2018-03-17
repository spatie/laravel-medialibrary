<?php

namespace Spatie\MediaLibrary;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Contracts\Support\Responsable;
use ZipStream\ZipStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStream implements Responsable
{
    /** string */
    protected $zipName;

    /** Illuminate\Support\Collection */
    protected $mediaItems;

    public static function create(string $zipName)
    {
        return new static($zipName);
    }

    public function __construct(string $zipName)
    {
        $this->zipName = $zipName;

        $this->mediaItems = collect();
    }

    public function addMedia(...$mediaItems)
    {
        collect($mediaItems)
            ->flatMap(function ($item) {
                if ($item instanceof Media) {
                    return [$item];
                }

                if ($item instanceof Collection) {
                    return $item->reduce(function(array $carry, Media $media) {
                        $carry[] = $media;

                        return $carry;
                    }, []);
                }

                return $item;
            })
            ->each(function(Media $media) {
                $this->mediaItems->push($media);
            });

        return $this;
    }

    public function getMediaItems(): Collection
    {
        return $this->mediaItems;
    }

    public function toResponse($request)
    {
        return new StreamedResponse(function () {
            $zip = new ZipStream($this->zipName);

            $this->mediaItems->each(function (Media $media) use ($zip) {
                $stream = $media->stream();

                $zip->addFileFromStream($media->file_name, $stream);

                fclose($stream);
            });

            $zip->finish();
        });
    }
}
