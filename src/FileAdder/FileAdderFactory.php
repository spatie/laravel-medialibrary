<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;

class FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model                        $subject
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string                              $key
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function createFromRequest(Model $subject, string $key)
    {
        return array_values(static::createMultipleFromRequest($subject, [$key]))[0];
    }

    /**
     * @param Model    $subject
     * @param string[] $keys
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder[]
     *
     * @throws RequestDoesNotHaveFile
     */
    public static function createMultipleFromRequest(Model $subject, array $keys = [])
    {
        $fileAdders = [];

        foreach ($keys as $key) {
            if (! request()->hasFile($key)) {
                throw RequestDoesNotHaveFile::create($key);
            }

            $fileAdders[] = static::create($subject, request()->file($key));
        }

        return $fileAdders;
    }

    /**
     * @param Model $subject
     *
     * @return FileAdder[]
     */
    public static function createAllFromRequest(Model $subject)
    {
        $allFileKeys = array_keys(request()->allFiles());

        return static::createMultipleFromRequest($subject, $allFileKeys);
    }
}
