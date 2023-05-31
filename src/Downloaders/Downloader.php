<?php

namespace Programic\MediaLibrary\Downloaders;

interface Downloader
{
    public function getTempFile(string $url): string;
}
