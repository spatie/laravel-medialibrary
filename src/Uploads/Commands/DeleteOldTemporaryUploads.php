<?php

namespace Spatie\MediaLibrary\Uploads\Commands;

use Illuminate\Console\Command;

class DeleteOldTemporaryUploads extends Command
{
    /** @var string */
    protected $signature = 'medialibrary:delete-old-temporary-uploads';

    /** @var string */
    protected $description = 'Delete old temporary uploads';

    public function handle()
    {
        $this->info('Start removing old temporary uploads...');

        $temporaryUploadClass = config('medialibrary.temporary_upload_model');

        $temporaryUploads = $temporaryUploadClass::old()->get();

        $temporaryUploads->each->delete();

        $this->comment($temporaryUploads.' old temporary upload(s) deleted!');
    }
}
