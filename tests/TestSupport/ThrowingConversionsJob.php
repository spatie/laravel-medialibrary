<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use RuntimeException;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;

class ThrowingConversionsJob extends PerformConversionsJob
{
    public function handle(FileManipulator $fileManipulator): bool
    {
        throw new RuntimeException('Conversion failed on purpose');
    }
}
