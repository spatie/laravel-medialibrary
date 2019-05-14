<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;

class FileAdderFactory
{
    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param \Illuminate\Database\Eloquent\Model|null $subject
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public static function create($file, $subject = null)
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile($file);
    }

    public static function createFromRequest(string $key, $subject = null): FileAdder
    {
        return static::createMultipleFromRequest([$key], $subject)->first();
    }

    public static function createMultipleFromRequest(array $keys = [], $subject = null): Collection
    {
        return collect($keys)
            ->map(function (string $key) use ($subject) {
                if (! request()->hasFile($key)) {
                    throw RequestDoesNotHaveFile::create($key);
                }

                $files = request()->file($key);

                if (! is_array($files)) {
                    return static::create($files, $subject);
                }

                return array_map(function ($file) use ($subject) {
                    return static::create($file, $subject);
                }, $files);
            })
            ->flatten();
    }

    public static function createAllFromRequest($subject = null): Collection
    {
        $fileKeys = array_keys(request()->allFiles());

        return static::createMultipleFromRequest($fileKeys, $subject);
    }
}
