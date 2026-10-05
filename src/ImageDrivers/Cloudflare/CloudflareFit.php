<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

enum CloudflareFit: string
{
    case ScaleDown = 'scale-down';
    case Contain = 'contain';
    case Cover = 'cover';
    case Crop = 'crop';
    case AspectCrop = 'aspect-crop';
    case Pad = 'pad';
    case Squeeze = 'squeeze';
    case ScaleUp = 'scale-up';
}
