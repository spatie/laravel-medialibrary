<?php

namespace Spatie\MediaLibrary\Models\TemporaryUpload;

use Carbon\Carbon;
use Spatie\MediaLibrary\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class TemporaryUpload extends BaseModel implements HasMediaConversions
{
    use HasMediaTrait;

    public static function findById(string $uploadId, string $sessionId): ?TemporaryUpload
    {
        return TemporaryUpload::where('id', $uploadId)
            ->where('session_id', session()->getId())
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
            'sessionId' => $sessionId
        ]);

        $temporaryUpload
            ->addMedia($file)
            ->toMediaCollection()
            ->save();

        return $temporaryUpload->fresh();
    }

    public function scopeOld(Builder $builder)
    {
        $builder->where('created_at', '<=', Carbon::now()->subDays(1)->toDateTimeString());
    }
}
