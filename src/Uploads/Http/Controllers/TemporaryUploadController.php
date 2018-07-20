<?php

namespace Spatie\MediaLibrary\Uploads\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Uploads\Http\Resources\MediaResource;

class TemporaryUploadController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:' . config('medialibrary.max_file_size'),
        ]);

        $temporaryUploadClass = config('medialibrary.temporary_upload_model');

        /** @var \Spatie\MediaLibrary\Uploads\Models\TemporaryUpload $temporaryUpload */
        $temporaryUpload = $temporaryUploadClass::createForFile(
            $request->file('file'),
            Session::getId()
        );

        return new MediaResource($temporaryUpload);
    }
}
