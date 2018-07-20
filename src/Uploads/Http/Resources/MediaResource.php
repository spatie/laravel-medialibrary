<?php

namespace Spatie\MediaLibrary\Uploads\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @mixin \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload
 */
class MediaResource extends Resource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            ['previewUrl' => $this->getFirstMediaUrl('default', 'preview')]
        );
    }
}
