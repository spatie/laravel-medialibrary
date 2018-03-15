<?php

namespace Spatie\MediaLibrary\Filesystem;

use Spatie\MediaLibrary\Models\Media;

interface Filesystem
{
    public function add(string $file, Media $media, ?string $targetFileName = null);

    public function copyToMediaLibrary(string $file, Media $media, ?string $type = null, ?string $targetFileName = null);

    public function addCustomRemoteHeaders(array $customRemoteHeaders);

    public function getRemoteHeadersForFile(string $file) : array;

    public function getStream(Media $media);

    public function copyFromMediaLibrary(Media $media, string $targetFile): string;

    public function renameFile(Media $media, string $oldName);

    public function removeAllFiles(Media $media);

    public function removeFile(Media $media, string $path);

    public function getMediaDirectory(Media $media, string $type) : string;

    public function getConversionDirectory(Media $media) : string;

    public function getResponsiveImagesDirectory(Media $media) : string;
}
