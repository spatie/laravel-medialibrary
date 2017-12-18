<?php

namespace Spatie\MediaLibrary\Models;

use Carbon\Carbon;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryUpload extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $guarded = [];

    public static function findById(string $uploadId, string $sessionId): ?TemporaryUpload
    {
        return static::where('id', $uploadId)
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
