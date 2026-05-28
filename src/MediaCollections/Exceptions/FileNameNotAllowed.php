<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

class FileNameNotAllowed extends FileCannotBeAdded
{
    public static function create(string $originalName, string $sanitizedName, ?string $extension = null): self
    {
        $reason = $extension !== null
            ? "The extension `{$extension}` is not allowed because it poses a security risk."
            : 'Its extension is not allowed because it poses a security risk.';

        return new static("The file name `{$originalName}` was sanitized to `{$sanitizedName}`. {$reason}");
    }
}
