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
    protected string $zipName;

    protected Collection $mediaItems;

    protected ArchiveOptions $zipOptions;

    public static function create(string $zipName)
    {
        return new static($zipName);
    }

    public function __construct(string $zipName)
    {
        $this->zipName = $zipName;

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
            'Content-Type'        => 'application/octet-stream',
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
        'fileNameInZip' => $this->getZipFileNamePrefix($this->mediaItems, $mediaItemIndex) . $this->getFileNameWithSuffix($this->mediaItems, $mediaItemIndex),
        'media'         => $media,
    ]);
    }

    protected function getFileNameWithSuffix(Collection $mediaItems, int $currentIndex): string
    {
        $fileNameCount = 0;

        foreach ($mediaItems as $index => $media) {
            if ($index >= $currentIndex) {
                break;
            }

            if ($this->getZipFileNameWithSuffix($mediaItems, $index) === $this->getZipFileNameWithSuffix($mediaItems, $currentIndex)) {
                $fileNameCount++;
            }
        }
        if ($fileNameCount === 0) {
            return $this->getZipFileName($mediaItems, $currentIndex);
        }

        $extension = pathinfo($mediaItems[$currentIndex]->file_name, PATHINFO_EXTENSION);

        return $this->getZipFileNameWithoutExtension($mediaItems, $currentIndex) . " ({$fileNameCount}).{$extension}";
    }

    protected function getZipFileNamePrefix(Collection $mediaItems, int $currentIndex): string
    {
        return $mediaItems[$currentIndex]->hasCustomProperty('zip_filename_prefix') ? $mediaItems[$currentIndex]->getCustomProperty('zip_filename_prefix') : '';
    }

    protected function getZipFileNameWithoutExtension(Collection $mediaItems, int $currentIndex): string
    {
        return $mediaItems[$currentIndex]->hasCustomProperty('zip_filename_suffix') ? $mediaItems[$currentIndex]->getCustomProperty('zip_filename_suffix') : pathinfo($mediaItems[$currentIndex]->file_name, PATHINFO_FILENAME);
    }

    protected function getZipFileNameWithSuffix(Collection $mediaItems, int $currentIndex): string
    {
        return $this->getZipFileNamePrefix($mediaItems, $currentIndex) . $this->getZipFileName($mediaItems, $currentIndex);
    }

    public function getZipFileName(Collection $mediaItems, int $currentIndex): string
    {
        return $this->getZipFileNameWithoutExtension($mediaItems, $currentIndex) . '.' . pathinfo($mediaItems[$currentIndex]->file_name, PATHINFO_EXTENSION);
    }
}
