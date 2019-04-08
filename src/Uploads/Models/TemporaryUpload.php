<?php

namespace Spatie\MediaLibrary\Uploads\Models;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class TemporaryUpload extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $guarded = [];

    public static function boot()
    {
        self::creating(function (TemporaryUpload $temporaryUpload) {
            if ($temporaryUpload->upload_id !== null) {
                return;
            }

            $temporaryUpload->upload_id = Uuid::uuid4();
        });
    }

    public static function findById(string $uploadId): ?self
    {
        return static::where('id', $uploadId)
            ->where('session_id', session()->getId())
            ->first();
    }

    public static function findBySessionId(string $uploadId, string $sessionId): ?self
    {
        return static::where('upload_id', $uploadId)
            ->where('session_id', $sessionId)
            ->first();
    }

    public static function createForFile(UploadedFile $file, string $sessionId): self
    {
        $temporaryUpload = static::create([
            'session_id' => $sessionId,
        ]);

        $temporaryUpload
            ->addMedia($file)
            ->toMediaCollection()
            ->save();

        return $temporaryUpload->fresh();
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('preview')
            ->width(config('medialibrary.temporary_upload_preview.width', 300))
            ->height(config('medialibrary.temporary_upload_preview.height', 300))
            ->nonQueued()
            ->optimize();
    }

    public function scopeOld(Builder $builder)
    {
        $builder->where('created_at', '<=', Carbon::now()->subDays(1)->toDateTimeString());
    }
}
