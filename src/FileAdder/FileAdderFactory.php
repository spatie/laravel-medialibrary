<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Helpers\TemporaryDirectory;
use Spatie\MediaLibrary\Uploads\TemporaryUploadRequestEntry;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;

class FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public static function create(Model $subject, $file)
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile($file);
    }

    public static function createFromRequest(Model $subject, string $key): FileAdder
    {
        return static::createMultipleFromRequest($subject, [$key])->first();
    }

    public static function createMultipleFromRequest(Model $subject, array $keys = []): Collection
    {
        return collect($keys)
            ->map(function (string $key) use ($subject) {
                if (! request()->hasFile($key)) {
                    throw RequestDoesNotHaveFile::create($key);
                }

                $files = request()->file($key);

                if (! is_array($files)) {
                    return static::create($subject, $files);
                }

                return array_map(function ($file) use ($subject) {
                    return static::create($subject, $file);
                }, $files);
            })
            ->flatten();
    }

    public static function createAllFromRequest(Model $subject): Collection
    {
        $fileKeys = array_keys(request()->allFiles());

        return static::createMultipleFromRequest($subject, $fileKeys);
    }

    public static function createFromTemporaryUploads(Model $subject, Collection $temporaryUploadRequestEntries): Collection
    {
        return $temporaryUploadRequestEntries->map(function (TemporaryUploadRequestEntry $temporaryUploadRequestEntry) use ($subject) {
            return self::createFromTemporaryUpload($subject, $temporaryUploadRequestEntry);
        });
    }

    public static function createFromTemporaryUpload(Model $subject, TemporaryUploadRequestEntry $temporaryUploadRequestEntry): FileAdder
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $temporaryUploadMedia = $temporaryUploadRequestEntry->media();

        $temporaryFile = $temporaryDirectory->path($temporaryUploadMedia->file_name);

        app(Filesystem::class)->copyFromMediaLibrary($temporaryUploadMedia, $temporaryFile);

        /** @var \Spatie\MediaLibrary\FileAdder\FileAdder $fileAdder */
        $fileAdder = $subject
            ->addMedia($temporaryFile)
            ->usingName($temporaryUploadRequestEntry->name);

        $fileAdder->afterFileHasBeenAdded(function () use ($temporaryDirectory, $temporaryUploadRequestEntry) {
            $temporaryDirectory->delete();

            $temporaryUploadRequestEntry->temporaryUpload->delete();
        });

        return $fileAdder;
    }
}
