<?php

namespace Spatie\MediaLibrary\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class FileAdderGroupFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model                            $subject
     * @param string[]|\Symfony\Component\HttpFoundation\File\UploadedFile[] $files
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdderGroup
     */
    public static function create(Model $subject, array $files)
    {
        return app(FileAdderGroup::class)
            ->setSubject($subject)
            ->setFiles($files);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string[]                            $keys
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdderGroup
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function createFromRequest(Model $subject, array $keys)
    {
//        foreach($keys as $key) {
//            if (! request()->hasFile($key)) {
//                throw RequestDoesNotHaveFile::create($key);
//            }
//        }

        $selectedFiles = collect(request()->allFiles())->only($keys)->toArray();

        return static::create($subject, $selectedFiles);
    }
}
