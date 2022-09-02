<?php

namespace Spatie\MediaLibrary\Support;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Option\Archive as ArchiveOptions;
use ZipStream\ZipStream;

class MediaStream implements Responsable
{
    protected Collection $mediaItems;

    protected ArchiveOptions $zipOptions;

    public static function create(string $zipName): self
    {
        return new static($zipName);
    }

    public function __construct(protected string $zipName)
    {
        $this->mediaItems = collect();

        $this->zipOptions = new ArchiveOptions();
    }

    public function useZipOptions(callable $zipOptionsCallable): self
    {
        $zipOptionsCallable($this->zipOptions);

        return $this;
    }

    public function addMedia(...$mediaItems): self
    {
        collect($mediaItems)
            ->flatMap(function ($item) {
                if ($item instanceof Media) {
                    return [$item];
                }

                if ($item instanceof Collection) {
                    return $item->reduce(function (array $carry, Media $media) {
                        $carry[] = $media;

                        return $carry;
                    }, []);
                }

                return $item;
            })
            ->each(fn (Media $media) => $this->mediaItems->push($media));

        return $this;
    }

    public function getMediaItems(): Collection
    {
        return $this->mediaItems;
    }

    public function toResponse($request): StreamedResponse
    {
        $headers = [
            'Content-Disposition' => "attachment; filename=\"{$this->zipName}\"",
            'Content-Type' => 'application/octet-stream',
        ];

        return new StreamedResponse(fn () => $this->getZipStream(), 200, $headers);
    }

    public function getZipStream(): ZipStream
    {
        $zip = new ZipStream($this->zipName, $this->zipOptions);

        $this->getZipStreamContents()->each(function (array $mediaInZip) use ($zip) {
            $stream = $mediaInZip['media']->stream();

            $zip->addFileFromStream($mediaInZip['fileNameInZip'], $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $zip->finish();

        return $zip;
    }

    protected function getZipStreamContents(): Collection
    {
        return $this->mediaItems->map(fn (Media $media, $mediaItemIndex) => [
            'fileNameInZip' => $this->getZipFileNamePrefix($this->mediaItems, $mediaItemIndex).$this->getFileNameWithSuffix($this->mediaItems, $mediaItemIndex),
            'media' => $media,
        ]);
    }

    protected function getFileNameWithSuffix(Collection $mediaItems, int $currentIndex): string
    {
        $fileNameCount = 0;

        $fileName = $mediaItems[$currentIndex]->file_name;

        foreach ($mediaItems as $index => $media) {
            if ($index >= $currentIndex) {
                break;
            }

            if ($this->getZipFileNamePrefix($mediaItems, $index).$media->file_name === $this->getZipFileNamePrefix($mediaItems, $currentIndex).$fileName) {
                $fileNameCount++;
            }
        }

        if ($fileNameCount === 0) {
            return $fileName;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$fileNameWithoutExtension} ({$fileNameCount}).{$extension}";
    }

    protected function getZipFileNamePrefix(Collection $mediaItems, int $currentIndex): string
    {
        return $mediaItems[$currentIndex]->hasCustomProperty('zip_filename_prefix') ? $mediaItems[$currentIndex]->getCustomProperty('zip_filename_prefix') : '';
    }
}
