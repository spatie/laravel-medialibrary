<?php

namespace Spatie\MediaLibrary\Uploads\Http\Resources;

class Company extends Resource
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
            'id' => $temporaryUpload->id,
            'previewUrl' => $temporaryUpload->getFirstMediaUrl('default', 'preview'),
        ];
    }
}
