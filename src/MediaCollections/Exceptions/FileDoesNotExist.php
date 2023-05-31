<?php

namespace Programic\MediaLibrary\MediaCollections\Exceptions;

class FileDoesNotExist extends FileCannotBeAdded
{
    public static function create(string $path): self
    {
        return new static("File `{$path}` does not exist");
    }
}
