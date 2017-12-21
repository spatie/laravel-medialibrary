<?php

namespace Spatie\MediaLibrary\Uploads\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TemporaryUpload extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'previewUrl' => $this->getFirstMediaUrl('default', 'preview'),
        ];
    }
}
