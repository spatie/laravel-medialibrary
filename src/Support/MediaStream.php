<?php

namespace Spatie\MediaLibrary\Support;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class MediaStream implements Responsable
{
    protected Collection $mediaItems;

    private array $nameCounters = [];

    protected array $zipOptions;

    public static function create(string $zipName): self
    {
        return new static($zipName);
    }

    public function __construct(protected string $zipName)
    {
        $this->mediaItems = collect();

        $this->zipOptions = [];
    }

    /**
     * @return $this
     */
    public function useZipOptions(callable $zipOptionsCallable): self
    {
        $zipOptionsCallable($this->zipOptions);

        return $this;
    }

    /**
     * @return $this
     */
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

    public function getZipStream(bool $finish = true): ZipStream
    {
        $this->zipOptions['outputName'] = $this->zipName;
        $zip = new ZipStream(...$this->zipOptions);

        $this->getZipStreamContents()->each(function (array $mediaInZip) use ($zip) {
            $stream = $mediaInZip['media']->stream();

            $zip->addFileFromStream($mediaInZip['fileNameInZip'], $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        if ($finish) {
            $zip->finish();
        }

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
        $fileName = $mediaItems[$currentIndex]->getDownloadFilename();

        $prefix = $this->getZipFileNamePrefix($mediaItems, $currentIndex);
        $key = $prefix.$fileName;

        $count = ($this->nameCounters[$key] ?? 0);
        $this->nameCounters[$key] = $count + 1;

        if ($count === 0) {
            return $fileName;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$fileNameWithoutExtension} ({$count}).{$extension}";
    }

    protected function getZipFileNamePrefix(Collection $mediaItems, int $currentIndex): string
    {
        return $mediaItems[$currentIndex]->hasCustomProperty('zip_filename_prefix') ? $mediaItems[$currentIndex]->getCustomProperty('zip_filename_prefix') : '';
    }
}
