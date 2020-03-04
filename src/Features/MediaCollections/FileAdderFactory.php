<?php

namespace Spatie\Medialibrary\Features\MediaCollections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Medialibrary\Features\MediaCollections\Exceptions\RequestDoesNotHaveFile;
use Spatie\Medialibrary\Support\RemoteFile;

class FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\Medialibrary\Features\MediaCollections\FileAdder
     */
    public static function create(Model $subject, $file): FileAdder
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile($file);
    }

    public static function createFromDisk(Model $subject, string $key, string $disk): FileAdder
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile(new RemoteFile($key, $disk));
    }

    public static function createFromRequest(Model $subject, string $key): FileAdder
    {
        return static::createMultipleFromRequest($subject, [$key])->first();
    }

    public static function createMultipleFromRequest(Model $subject, array $keys = []): Collection
    {
        return collect($keys)
            ->map(function (string $key) use ($subject) {
                $search = ['[', ']', '"', "'"];
                $replace = ['.', '', '', ''];

                $key = str_replace($search, $replace, $key);

                if (! request()->hasFile($key)) {
                    throw RequestDoesNotHaveFile::create($key);
                }

                $files = request()->file($key);

                if (! is_array($files)) {
                    return static::create($subject, $files);
                }

                return array_map(fn($file) => static::create($subject, $file), $files);
            })->flatten();
    }

    public static function createAllFromRequest(Model $subject): Collection
    {
        $fileKeys = array_keys(request()->allFiles());

        return static::createMultipleFromRequest($subject, $fileKeys);
    }
}
