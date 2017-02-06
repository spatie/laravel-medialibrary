<?php

namespace Spatie\MediaLibrary\Filesystem;

use Spatie\MediaLibrary\Media;

interface Filesystem
{
    public function add(string $file, Media $media, string $targetFileName = '');

    public function copyToMediaLibrary(string $file, Media $media, bool $conversions = false, string $targetFileName = '');

    public function addCustomRemoteHeaders(array $customRemoteHeaders);

    public function getRemoteHeadersForFile(string $file) : array;

    public function copyFromMediaLibrary(Media $media, string $targetFile): string;

    public function renameFile(Media $media, string $oldName);

    public function removeFiles(Media $media);

    public function getMediaDirectory(Media $media, bool $conversion = false) : string;

    public function getConversionDirectory(Media $media) : string;
}
