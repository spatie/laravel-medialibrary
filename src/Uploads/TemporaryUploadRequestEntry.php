<?php

namespace Spatie\MediaLibrary\Uploads;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Uploads\Models\TemporaryUpload;

class TemporaryUploadRequestEntry
{
    /** @var \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload */
    public $temporaryUpload;

    /** @var string */
    public $name;

    public static function createFromRequest(Request $request, string $keyName): Collection
    {
        return collect($request->get($keyName))
            ->map(function ($temporaryUploadProperties) {
                $temporaryUpload = TemporaryUpload::findBySessionId($temporaryUploadProperties['upload_id'], session()->getId());

                //TODO: throw exception when temporary upload is not found

                return new static($temporaryUpload, $temporaryUploadProperties['name']);
            });
    }

    public function __construct(TemporaryUpload $temporaryUpload, string $name)
    {
        $this->temporaryUpload = $temporaryUpload;

        $this->name = $name;
    }

    public function media(): Media
    {
        return $this->temporaryUpload->getFirstMedia();
    }
}
