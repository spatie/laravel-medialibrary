<?php

namespace Spatie\MediaLibrary\Uploads\Commands;

use App\Models\TemporaryUpload;

use Illuminate\Console\Command;

class DeleteOldTemporaryUploads extends Command
{
    protected $signature = 'medialibrary:delete-old-temporary-uploads';

    protected $description = 'Delete old temporary uploads';

    public function handle()
    {
        $this->info('Start removing old temporary uploads...');

        $temporaryUploadClass = config('medialibrary.uploads.temporary_upload_model');

        $temporaryUploads = $temporaryUploadClass::old()->get();

        $temporaryUploads->each->delete();

        $this->comment("{$temporaryUploads} old temporary upload(s) deleted!");
    }
}
