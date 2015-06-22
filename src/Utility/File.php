<?php

namespace Spatie\MediaLibrary\Utility;

class File
{
    public static function getHumanReadableFileSize($size)
    {
        // Less than 1 KB
        if ($size >= 0 && $size < 1024) {
            return $size . " bytes";
        }

        // Less than 1 MB
        if ($size >=1024 && $size < 1048576) {
            $size = $size / 1024;
            return round($size) . " KB";
        }

        // More than 1 MB, but less than 10
        if ($size >=1048576 && $size < 10485760) {
            $size = $size / 1048576;
            return round($size, 1) . " MB";
        }
        
        // More than 10 MB
        $size = $size / 1048576;
        return round($size) . " MB";
    }
}
