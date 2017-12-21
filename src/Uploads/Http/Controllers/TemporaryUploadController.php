<?php

namespace Spatie\MediaLibrary\Uploads\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\Session;
use Spatie\MediaLibrary\Uploads\Http\Resources\TemporaryUpload as TemporaryUploadResource;

class TemporaryUploadController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:' . config('medialibrary.max_file_size'),
        ]);

        $temporaryUploadClass = config('medialibrary.uploads.temporary_upload_model');

        $temporaryUpload = $temporaryUploadClass::createForFile(
            $request->file('file'),
            Session::getId()
        );

        return new TemporaryUploadResource($temporaryUpload);
    }
}
