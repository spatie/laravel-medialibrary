<?php

namespace Spatie\MediaLibrary\Test;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class TestPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $game = TestModel::find($media->model_id);

        $fileFolder = md5($media->id);
        $path = "{$game->id}/{$fileFolder}/";

        return $path;
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'/convertions/';
    }
}
