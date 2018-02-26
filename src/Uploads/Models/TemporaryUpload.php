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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryUpload extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $guarded = [];

    public static function findByUploadId(string $uploadId, string $sessionId): ?TemporaryUpload
    {
        return static::where('upload_id', $uploadId)
            ->where('session_id', $sessionId)
            ->first();
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300)
            ->nonQueued()
            ->optimize();
    }

    public static function createForFile(UploadedFile $file, string $sessionId): TemporaryUpload
    {
        $temporaryUpload = static::create([
            'upload_id' => Uuid::uuid4(),
            'session_id' => $sessionId,
        ]);

        $temporaryUpload
            ->addMedia($file)
            ->toMediaCollection()
            ->save();

        return $temporaryUpload->refresh();
    }

    public function scopeOld(Builder $builder)
    {
        $builder->where('created_at', '<=', Carbon::now()->subDays(1));
    }
}
