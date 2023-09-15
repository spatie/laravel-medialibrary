<?php

namespace Spatie\MediaLibrary\Support;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory as SupportTemporaryDirectory;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class TemporaryFile
{
    protected string $file;

    protected TemporaryDirectory $temporaryDirectory;

    protected Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function getFile(): string
    {
        if (! isset($this->file)) {
            $this->copyFile();
        }

        return $this->file;
    }

    protected function copyFile(): void
    {
        if (! isset($this->temporaryDirectory)) {
            $this->createTemporaryDirectory();
        }

        $this->file = app(Filesystem::class)->copyFromMediaLibrary(
            $this->media,
            $this->temporaryDirectory->path(Str::random(32) . '.' . $this->media->extension)
        );
    }

    protected function createTemporaryDirectory(): void
    {
        $this->temporaryDirectory = SupportTemporaryDirectory::create();
    }

    public function __destruct()
    {
        if (isset($this->temporaryDirectory)) {
            $this->temporaryDirectory->delete();
        }
    }
}
