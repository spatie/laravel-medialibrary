<?php

namespace Spatie\MediaLibrary\Uploads\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TemporaryUploadController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:'.config('medialibrary.max_file_size'),
        ]);

        $temporaryUploadClass = config('medialibrary.temporary_upload_model');

        /** @var \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload $temporaryUpload */
        $temporaryUpload = $temporaryUploadClass::createForFile(
            $request->file('file'),
            Session::getId()
        );

        $media = $temporaryUpload->getFirstMedia();

        return array_merge(
            $media->toArray(),
            [
                'preview_url' => $media->hasGeneratedConversion('preview')
                    ? $media->getUrl('preview')
                    : null,
                'upload_id' => $temporaryUpload->upload_id,
            ]
        );
    }
}
