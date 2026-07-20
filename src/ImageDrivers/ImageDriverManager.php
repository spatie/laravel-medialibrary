<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;
use ReflectionNamedType;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareDeliveryImageDriver;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImageDriver;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidImageDriver;

class ImageDriverManager
{
    /** @var array<string, MediaImageDriver> */
    protected array $resolved = [];

    /** @var array<string, Closure(array): MediaImageDriver> */
    protected array $customCreators = [];

    public function driver(?string $name = null): MediaImageDriver
    {
        $name ??= $this->defaultDriverName();

        return $this->resolved[$name] ??= $this->resolve($name);
    }

    /**
     * @param  Closure(array): MediaImageDriver  $creator
     */
    public function extend(string $name, Closure $creator): void
    {
        $this->customCreators[$name] = $creator;

        unset($this->resolved[$name]);
    }

    public function forConversion(Conversion $conversion): MediaImageDriver
    {
        $name = $conversion->getImageDriverName()
            ?? $this->inferDriverName($conversion->getManipulationClosure())
            ?? $this->defaultDriverName();

        return $this->driver($name);
    }

    public function isVirtual(Conversion $conversion): bool
    {
        return $this->forConversion($conversion) instanceof ResolvesConversionUrls;
    }

    public function defaultDriverName(): string
    {
        $configured = config('media-library.image_driver', 'gd');

        return $this->isSpatieEngine($configured) ? 'spatie' : $configured;
    }

    /**
     * The spatie/image engine to use for pixel work (conversions on the spatie
     * driver, responsive images, dimension detection).
     */
    public function spatieEngine(): string
    {
        $configured = config('media-library.image_driver', 'gd');

        if ($this->isSpatieEngine($configured)) {
            return $configured;
        }

        return config('media-library.image_drivers.spatie.engine') ?? 'gd';
    }

    protected function isSpatieEngine(string $value): bool
    {
        return in_array($value, ['gd', 'imagick', 'vips'], true);
    }

    protected function inferDriverName(?SerializableClosure $closure): ?string
    {
        if (! $closure) {
            return null;
        }

        $parameters = (new ReflectionFunction($closure->getClosure()))->getParameters();

        $type = ($parameters[0] ?? null)?->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        foreach (array_keys($this->driverConfigs()) as $name) {
            $imageClass = $this->driver($name)->imageClass();

            if ($type->getName() === $imageClass || is_subclass_of($type->getName(), $imageClass)) {
                return $name;
            }
        }

        return null;
    }

    protected function resolve(string $name): MediaImageDriver
    {
        $config = $this->driverConfigs()[$name] ?? null;

        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($config ?? []);
        }

        if (! $config || ! isset($config['driver'])) {
            throw InvalidImageDriver::unknown($name);
        }

        $driverClass = $config['driver'];

        return new $driverClass($config);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function driverConfigs(): array
    {
        $defaults = [
            'spatie' => [
                'driver' => SpatieImageDriver::class,
                'engine' => $this->spatieEngine(),
            ],
            'cloudflare' => [
                'driver' => CloudflareImageDriver::class,
            ],
            'cloudflare-delivery' => [
                'driver' => CloudflareDeliveryImageDriver::class,
            ],
        ];

        $configs = array_replace($defaults, config('media-library.image_drivers') ?? []);

        // The legacy `image_driver` engine values keep selecting the engine of
        // the spatie driver, as they did before drivers existed.
        $legacy = config('media-library.image_driver', 'gd');

        if ($this->isSpatieEngine($legacy)) {
            $configs['spatie']['engine'] = $legacy;
        }

        return $configs;
    }
}
