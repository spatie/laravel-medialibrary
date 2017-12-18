<?php

namespace Spatie\MediaLibrary\Uploads\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryUpload;
use App\Http\Resources\TemporaryUpload as TemporaryUploadResource;

class TemporaryUploadController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:' . config('medialibrary.max_file_size'),
        ]);

        $temporaryUpload = TemporaryUpload::createForFile(
            $request->file('file'),
            session()->getId()
        );

        return new TemporaryUploadResource($temporaryUpload);
    }
}
