<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

/**
 * The image object for the Cloudflare drivers. Manipulating it accumulates
 * Cloudflare transformation parameters. Only parameters Cloudflare supports
 * exist as methods, so unsupported operations surface in your editor and in
 * static analysis instead of at runtime.
 *
 */
class CloudflareImage
{
    /** @var array<string, string|int|float> */
    protected array $parameters = [];

    public function width(int $width): self
    {
        return $this->parameter('width', $width);
    }

    public function format(CloudflareFormat $format): self
    {
        return $this->parameter('format', $format->value);
    }

    public function quality(int $quality): self
    {
        return $this->parameter('quality', $quality);
    }

    public function height(int $height): self
    {
        return $this->parameter('height', $height);
    }

    public function fit(CloudflareFit $fit): self
    {
        return $this->parameter('fit', $fit->value);
    }

    /**
     * Crop focal point: `auto`, `face`, a side (`left`, `right`, `top`,
     * `bottom`), or coordinates like `0.5x0.2`.
     */
    public function gravity(string $gravity): self
    {
        return $this->parameter('gravity', $gravity);
    }

    public function blur(int $radius): self
    {
        return $this->parameter('blur', $radius);
    }

    public function sharpen(float $amount): self
    {
        return $this->parameter('sharpen', $amount);
    }

    public function brightness(float $value): self
    {
        return $this->parameter('brightness', $value);
    }

    public function contrast(float $value): self
    {
        return $this->parameter('contrast', $value);
    }

    public function saturation(float $value): self
    {
        return $this->parameter('saturation', $value);
    }

    public function rotate(int $degrees): self
    {
        return $this->parameter('rotate', $degrees);
    }

    /**
     * Mirror the image: `h`, `v`, or `hv`.
     */
    public function flip(string $direction): self
    {
        return $this->parameter('flip', $direction);
    }

    public function dpr(int $devicePixelRatio): self
    {
        return $this->parameter('dpr', $devicePixelRatio);
    }

    /**
     * Fill color for transparent pixels, for example `#ffffff`.
     */
    public function background(string $color): self
    {
        return $this->parameter('background', $color);
    }

    /**
     * Background removal: pass `foreground` to keep only the foreground.
     */
    public function segment(string $mode): self
    {
        return $this->parameter('segment', $mode);
    }

    /**
     * Enlargement algorithm when upscaling: `interpolate` or `generate`.
     */
    public function upscale(string $algorithm): self
    {
        return $this->parameter('upscale', $algorithm);
    }

    /**
     * How close a face crop should be, between 0.0 and 1.0.
     */
    public function zoom(float $faceZoom): self
    {
        return $this->parameter('zoom', $faceZoom);
    }

    public function preserveAnimation(bool $preserve = true): self
    {
        return $this->parameter('anim', $preserve ? 'true' : 'false');
    }

    /**
     * EXIF handling: `copyright`, `keep`, or `none`.
     */
    public function metadata(string $mode): self
    {
        return $this->parameter('metadata', $mode);
    }

    /**
     * Escape hatch for Cloudflare parameters that have no dedicated method.
     */
    public function parameter(string $name, string|int|float $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /** @return array<string, string|int|float> */
    public function toParameters(): array
    {
        ksort($this->parameters);

        return $this->parameters;
    }
}
