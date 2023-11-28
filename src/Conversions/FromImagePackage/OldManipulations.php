<?php

namespace Spatie\Image;

use League\Glide\Filesystem\FileNotFoundException;
use ReflectionClass;
use Spatie\Image\Exceptions\InvalidManipulation;

class OldManipulations
{
    public const CROP_TOP_LEFT = 'crop-top-left';
    public const CROP_TOP = 'crop-top';
    public const CROP_TOP_RIGHT = 'crop-top-right';
    public const CROP_LEFT = 'crop-left';
    public const CROP_CENTER = 'crop-center';
    public const CROP_RIGHT = 'crop-right';
    public const CROP_BOTTOM_LEFT = 'crop-bottom-left';
    public const CROP_BOTTOM = 'crop-bottom';
    public const CROP_BOTTOM_RIGHT = 'crop-bottom-right';

    public const ORIENTATION_AUTO = 'auto';
    public const ORIENTATION_0 = 0;
    public const ORIENTATION_90 = 90;
    public const ORIENTATION_180 = 180;
    public const ORIENTATION_270 = 270;

    public const FLIP_HORIZONTALLY = 'h';
    public const FLIP_VERTICALLY = 'v';
    public const FLIP_BOTH = 'both';

    public const FIT_CONTAIN = 'contain';
    public const FIT_MAX = 'max';
    public const FIT_FILL = 'fill';
    public const FIT_FILL_MAX = 'fill-max';
    public const FIT_STRETCH = 'stretch';
    public const FIT_CROP = 'crop';

    public const BORDER_OVERLAY = 'overlay';
    public const BORDER_SHRINK = 'shrink';
    public const BORDER_EXPAND = 'expand';

    public const FORMAT_JPG = 'jpg';
    public const FORMAT_PJPG = 'pjpg';
    public const FORMAT_PNG = 'png';
    public const FORMAT_GIF = 'gif';
    public const FORMAT_WEBP = 'webp';
    public const FORMAT_AVIF = 'avif';
    public const FORMAT_TIFF = 'tiff';

    public const FILTER_GREYSCALE = 'greyscale';
    public const FILTER_SEPIA = 'sepia';

    public const UNIT_PIXELS = 'px';
    public const UNIT_PERCENT = '%';

    public const POSITION_TOP_LEFT = 'top-left';
    public const POSITION_TOP = 'top';
    public const POSITION_TOP_RIGHT = 'top-right';
    public const POSITION_LEFT = 'left';
    public const POSITION_CENTER = 'center';
    public const POSITION_RIGHT = 'right';
    public const POSITION_BOTTOM_LEFT = 'bottom-left';
    public const POSITION_BOTTOM = 'bottom';
    public const POSITION_BOTTOM_RIGHT = 'bottom-right';

    protected ManipulationSequence $manipulationSequence;

    public function __construct(array $manipulations = [])
    {
        if (! $this->hasMultipleConversions($manipulations)) {
            $manipulations = [$manipulations];
        }

        foreach ($manipulations as $manipulation) {
            $this->manipulationSequence = new ManipulationSequence($manipulation);
        }
    }

    public static function create(array $manipulations = []): Manipulations
    {
        return new self($manipulations);
    }

    /**
     * @throws InvalidManipulation
     */
    public function orientation(string $orientation): static
    {
        if (! $this->validateManipulation($orientation, 'orientation')) {
            throw InvalidManipulation::invalidParameter(
                'orientation',
                $orientation,
                $this->getValidManipulationOptions('orientation')
            );
        }

        return $this->addManipulation('orientation', $orientation);
    }

    /**
     * @throws InvalidManipulation
     */
    public function flip(string $orientation): static
    {
        if (! $this->validateManipulation($orientation, 'flip')) {
            throw InvalidManipulation::invalidParameter(
                'flip',
                $orientation,
                $this->getValidManipulationOptions('flip')
            );
        }

        return $this->addManipulation('flip', $orientation);
    }

    /**
     * @throws InvalidManipulation
     */
    public function crop(string $cropMethod, int $width, int $height): static
    {
        if (! $this->validateManipulation($cropMethod, 'crop')) {
            throw InvalidManipulation::invalidParameter(
                'cropmethod',
                $cropMethod,
                $this->getValidManipulationOptions('crop')
            );
        }

        $this->width($width);
        $this->height($height);

        return $this->addManipulation('crop', $cropMethod);
    }

    /**
     * @param int $focalX Crop center X in percent
     * @param int $focalY Crop center Y in percent
     *
     * @throws InvalidManipulation
     */
    public function focalCrop(int $width, int $height, int $focalX, int $focalY, float $zoom = 1): static
    {
        if ($zoom < 1 || $zoom > 100) {
            throw InvalidManipulation::valueNotInRange('zoom', $zoom, 1, 100);
        }

        $this->width($width);
        $this->height($height);

        return $this->addManipulation('crop', "crop-{$focalX}-{$focalY}-{$zoom}");
    }

    /**
     * @throws InvalidManipulation
     */
    public function manualCrop(int $width, int $height, int $x, int $y): static
    {
        if ($width < 0) {
            throw InvalidManipulation::invalidWidth($width);
        }

        if ($height < 0) {
            throw InvalidManipulation::invalidWidth($height);
        }

        return $this->addManipulation('manualCrop', "{$width},{$height},{$x},{$y}");
    }

    /**
     * @throws InvalidManipulation
     */
    public function width(int $width): static
    {
        if ($width < 0) {
            throw InvalidManipulation::invalidWidth($width);
        }

        return $this->addManipulation('width', (string)$width);
    }

    /**
     * @throws InvalidManipulation
     */
    public function height(int $height): static
    {
        if ($height < 0) {
            throw InvalidManipulation::invalidHeight($height);
        }

        return $this->addManipulation('height', (string)$height);
    }

    /**
     * @throws InvalidManipulation
     */
    public function fit(string $fitMethod, ?int $width = null, ?int $height = null): static
    {
        if (! $this->validateManipulation($fitMethod, 'fit')) {
            throw InvalidManipulation::invalidParameter(
                'fit',
                $fitMethod,
                $this->getValidManipulationOptions('fit')
            );
        }

        if ($width === null && $height === null) {
            throw new InvalidManipulation('Width or height or both must be provided');
        }

        if ($width !== null) {
            $this->width($width);
        }

        if ($height !== null) {
            $this->height($height);
        }

        return $this->addManipulation('fit', $fitMethod);
    }

    /**
     * @param int $ratio A value between 1 and 8
     *
     * @throws InvalidManipulation
     */
    public function devicePixelRatio(int $ratio): static
    {
        if ($ratio < 1 || $ratio > 8) {
            throw InvalidManipulation::valueNotInRange('ratio', $ratio, 1, 8);
        }

        return $this->addManipulation('devicePixelRatio', (string)$ratio);
    }

    /**
     * @param int $brightness A value between -100 and 100
     *
     * @throws InvalidManipulation
     */
    public function brightness(int $brightness): static
    {
        if ($brightness < -100 || $brightness > 100) {
            throw InvalidManipulation::valueNotInRange('brightness', $brightness, -100, 100);
        }

        return $this->addManipulation('brightness', (string)$brightness);
    }

    /**
     * @param float $gamma A value between 0.01 and 9.99
     *
     * @throws InvalidManipulation
     */
    public function gamma(float $gamma): static
    {
        if ($gamma < 0.01 || $gamma > 9.99) {
            throw InvalidManipulation::valueNotInRange('gamma', $gamma, 0.01, 9.00);
        }

        return $this->addManipulation('gamma', (string)$gamma);
    }

    /**
     * @param int $contrast A value between -100 and 100
     *
     * @throws InvalidManipulation
     */
    public function contrast(int $contrast): static
    {
        if ($contrast < -100 || $contrast > 100) {
            throw InvalidManipulation::valueNotInRange('contrast', $contrast, -100, 100);
        }

        return $this->addManipulation('contrast', (string)$contrast);
    }

    /**
     * @param int $sharpen A value between 0 and 100
     *
     * @throws InvalidManipulation
     */
    public function sharpen(int $sharpen): static
    {
        if ($sharpen < 0 || $sharpen > 100) {
            throw InvalidManipulation::valueNotInRange('sharpen', $sharpen, 0, 100);
        }

        return $this->addManipulation('sharpen', (string)$sharpen);
    }

    /**
     * @param int $blur A value between 0 and 100
     *
     * @throws InvalidManipulation
     */
    public function blur(int $blur): static
    {
        if ($blur < 0 || $blur > 100) {
            throw InvalidManipulation::valueNotInRange('blur', $blur, 0, 100);
        }

        return $this->addManipulation('blur', (string)$blur);
    }

    /**
     * @param int $pixelate A value between 0 and 1000
     *
     * @throws InvalidManipulation
     */
    public function pixelate(int $pixelate): static
    {
        if ($pixelate < 0 || $pixelate > 1000) {
            throw InvalidManipulation::valueNotInRange('pixelate', $pixelate, 0, 1000);
        }

        return $this->addManipulation('pixelate', (string)$pixelate);
    }

    /**
     * @throws InvalidManipulation
     */
    public function greyscale(): static
    {
        return $this->filter('greyscale');
    }

    /**
     * @throws InvalidManipulation
     */
    public function sepia(): static
    {
        return $this->filter('sepia');
    }

    public function background(string $colorName): static
    {
        return $this->addManipulation('background', $colorName);
    }

    /**
     * @throws InvalidManipulation
     */
    public function border(int $width, string $color, string $borderType = 'overlay'): static
    {
        if ($width < 0) {
            throw InvalidManipulation::invalidWidth($width);
        }

        if (! $this->validateManipulation($borderType, 'border')) {
            throw InvalidManipulation::invalidParameter(
                'border',
                $borderType,
                $this->getValidManipulationOptions('border')
            );
        }

        return $this->addManipulation('border', "{$width},{$color},{$borderType}");
    }

    /**
     * @throws InvalidManipulation
     */
    public function quality(int $quality): static
    {
        if ($quality < 0 || $quality > 100) {
            throw InvalidManipulation::valueNotInRange('quality', $quality, 0, 100);
        }

        return $this->addManipulation('quality', (string)$quality);
    }

    /**
     * @throws InvalidManipulation
     */
    public function format(string $format): static
    {
        if (! $this->validateManipulation($format, 'format')) {
            throw InvalidManipulation::invalidParameter(
                'format',
                $format,
                $this->getValidManipulationOptions('format')
            );
        }

        return $this->addManipulation('format', $format);
    }

    /**
     * @throws InvalidManipulation
     */
    protected function filter(string $filterName): static
    {
        if (! $this->validateManipulation($filterName, 'filter')) {
            throw InvalidManipulation::invalidParameter(
                'filter',
                $filterName,
                $this->getValidManipulationOptions('filter')
            );
        }

        return $this->addManipulation('filter', $filterName);
    }

    /**
     * @throws FileNotFoundException
     */
    public function watermark(string $filePath): static
    {
        if (! file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $this->addManipulation('watermark', $filePath);

        return $this;
    }

    /**
     * @param int    $width The width of the watermark in pixels (default) or percent.
     * @param string $unit  The unit of the `$width` parameter. Use `Manipulations::UNIT_PERCENT` or `Manipulations::UNIT_PIXELS`.
     */
    public function watermarkWidth(int $width, string $unit = 'px'): static
    {
        $width = ($unit === static::UNIT_PERCENT ? $width.'w' : $width);

        return $this->addManipulation('watermarkWidth', (string)$width);
    }

    /**
     * @param int    $height The height of the watermark in pixels (default) or percent.
     * @param string $unit   The unit of the `$height` parameter. Use `Manipulations::UNIT_PERCENT` or `Manipulations::UNIT_PIXELS`.
     */
    public function watermarkHeight(int $height, string $unit = 'px'): static
    {
        $height = ($unit === static::UNIT_PERCENT ? $height.'h' : $height);

        return $this->addManipulation('watermarkHeight', (string)$height);
    }

    /**
     * @param string $fitMethod How is the watermark fitted into the watermarkWidth and watermarkHeight properties.
     *
     * @throws InvalidManipulation
     */
    public function watermarkFit(string $fitMethod): static
    {
        if (! $this->validateManipulation($fitMethod, 'fit')) {
            throw InvalidManipulation::invalidParameter(
                'watermarkFit',
                $fitMethod,
                $this->getValidManipulationOptions('fit')
            );
        }

        return $this->addManipulation('watermarkFit', $fitMethod);
    }

    /**
     * @param int $xPadding         How far is the watermark placed from the left and right edges of the image.
     * @param int|null $yPadding    How far is the watermark placed from the top and bottom edges of the image.
     * @param string $unit          Unit of the padding values. Use `Manipulations::UNIT_PERCENT` or `Manipulations::UNIT_PIXELS`.
     */
    public function watermarkPadding(int $xPadding, int $yPadding = null, string $unit = 'px'): static
    {
        $yPadding = $yPadding ?? $xPadding;

        $xPadding = ($unit === static::UNIT_PERCENT ? $xPadding.'w' : $xPadding);
        $yPadding = ($unit === static::UNIT_PERCENT ? $yPadding.'h' : $yPadding);

        $this->addManipulation('watermarkPaddingX', (string)$xPadding);
        $this->addManipulation('watermarkPaddingY', (string)$yPadding);

        return $this;
    }

    /**
     * @throws InvalidManipulation
     */
    public function watermarkPosition(string $position): static
    {
        if (! $this->validateManipulation($position, 'position')) {
            throw InvalidManipulation::invalidParameter(
                'watermarkPosition',
                $position,
                $this->getValidManipulationOptions('position')
            );
        }

        return $this->addManipulation('watermarkPosition', $position);
    }

    /**
     * Sets the opacity of the watermark. Only works with the `imagick` driver.
     *
     * @param int $opacity A value between 0 and 100.
     *
     * @throws InvalidManipulation
     */
    public function watermarkOpacity(int $opacity): static
    {
        if ($opacity < 0 || $opacity > 100) {
            throw InvalidManipulation::valueNotInRange('opacity', $opacity, 0, 100);
        }

        return $this->addManipulation('watermarkOpacity', (string)$opacity);
    }

    /**
     * Shave off some kilobytes by optimizing the image.
     */
    public function optimize(array $optimizationOptions = []): static
    {
        return $this->addManipulation('optimize', json_encode($optimizationOptions));
    }

    public function apply(): static
    {
        $this->manipulationSequence->startNewGroup();

        return $this;
    }

    public function toArray(): array
    {
        return $this->manipulationSequence->toArray();
    }

    /**
     * Checks if the given manipulations has arrays inside or not.
     */
    private function hasMultipleConversions(array $manipulations): bool
    {
        foreach ($manipulations as $manipulation) {
            if (isset($manipulation[0]) && is_array($manipulation[0])) {
                return true;
            }
        }

        return false;
    }

    public function removeManipulation(string $name): void
    {
        $this->manipulationSequence->removeManipulation($name);
    }

    public function hasManipulation(string $manipulationName): bool
    {
        return ! is_null($this->getManipulationArgument($manipulationName));
    }

    public function getManipulationArgument(string $manipulationName)
    {
        foreach ($this->manipulationSequence->getGroups() as $manipulationSet) {
            if (array_key_exists($manipulationName, $manipulationSet)) {
                return $manipulationSet[$manipulationName];
            }
        }
    }

    protected function addManipulation(string $manipulationName, string $manipulationArgument): static
    {
        $this->manipulationSequence->addManipulation($manipulationName, $manipulationArgument);

        return $this;
    }

    public function mergeManipulations(self $manipulations): static
    {
        $this->manipulationSequence->merge($manipulations->manipulationSequence);

        return $this;
    }

    public function getManipulationSequence(): ManipulationSequence
    {
        return $this->manipulationSequence;
    }

    protected function validateManipulation(string $value, string $constantNamePrefix): bool
    {
        return in_array($value, $this->getValidManipulationOptions($constantNamePrefix));
    }

    protected function getValidManipulationOptions(string $manipulation): array
    {
        $options = (new ReflectionClass(static::class))->getConstants();

        return array_filter($options, function ($value, $name) use ($manipulation) {
            return str_starts_with($name, mb_strtoupper($manipulation));
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function isEmpty(): bool
    {
        return $this->manipulationSequence->isEmpty();
    }

    /*
     * Get the first manipulation with the given name.
     *
     * @return mixed
     */
    public function getFirstManipulationArgument(string $manipulationName)
    {
        return $this->manipulationSequence->getFirstManipulationArgument($manipulationName);
    }
}
