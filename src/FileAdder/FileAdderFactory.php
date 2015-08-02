<?php

namespace Spatie\MediaLibrary\FileAdder;

class FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model                        $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return mixed
     */
    public static function create($subject, $file)
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile($file);
    }
}
