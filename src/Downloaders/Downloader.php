<?php

namespace Spatie\MediaLibrary\Downloaders;

interface Downloader
{
    public function getTempFile(string $url): string;
}
