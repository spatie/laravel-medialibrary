<?php

namespace Spatie\MediaLibrary\Downloaders;

interface Downloader
{
    public function getTempFile($url);
}
