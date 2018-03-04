<?php

namespace Spatie\MediaLibrary\Uploads\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MediaResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            ['previewUrl' => $this->model->getFirstMediaUrl('default', 'preview')]
        );
    }
}
