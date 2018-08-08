<?php

namespace Spatie\MediaLibrary\Uploads\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @mixin \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload
 */
class TemporaryUploadResource extends Resource
{
    public function toArray($request): array
    {
        $firstMedia = $this->getFirstMedia();

        return array_merge(
            parent::toArray($request),
            [
                'name' => $firstMedia->name,
                'previewUrl' => $firstMedia->hasGeneratedConversion('preview')
                    ? $firstMedia->getUrl('preview')
                    : null,
            ]
        );
    }
}
