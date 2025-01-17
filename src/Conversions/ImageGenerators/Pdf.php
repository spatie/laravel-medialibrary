<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Support\Collection;
use Imagick;
use Spatie\MediaLibrary\Conversions\Conversion;

class Pdf extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $pageNumber = $conversion ? $conversion->getPdfPageNumber() : 1;

        if ($this->usesPdfToImageV3()) {
            (new \Spatie\PdfToImage\Pdf($file))->selectPage($pageNumber)->save($imageFile);
        } else {
            (new \Spatie\PdfToImage\Pdf($file))->setPage($pageNumber)->saveImage($imageFile);
        }

        return $imageFile;
    }

    private function usesPdfToImageV3(): bool
    {
        return InstalledVersions::satisfies(new VersionParser, 'spatie/pdf-to-image', '^3.0');
    }

    public function requirementsAreInstalled(): bool
    {
        if (! class_exists(Imagick::class)) {
            return false;
        }

        if (! class_exists(\Spatie\PdfToImage\Pdf::class)) {
            return false;
        }

        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect(['pdf']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['application/pdf']);
    }
}
