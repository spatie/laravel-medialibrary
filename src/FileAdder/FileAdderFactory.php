<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class FileAdderFactory
{
    /**
     * @param Model                                                      $subject
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
     * @param Model  $subject
     * @param string $key
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function createFromRequest(Model $subject, string $key)
    {
        $file = request()->file($key);

        if (is_null($file)) {
            throw FileCannotBeAdded::requestDoesNotHaveFile($key);
        }

        return static::create($subject, $file);
    }
}
