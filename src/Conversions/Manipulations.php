<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\ColorFormat;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\FlipDirection;

/** @mixin \Spatie\Image\Drivers\ImageDriver */
class Manipulations
{
    protected array $manipulations = [];

    public function __construct(array $manipulations = [])
    {
        $this->manipulations = $manipulations;
    }

    public function __call(string $method, array $parameters): self
    {
        $this->addManipulation($method, $parameters);

        return $this;
    }

    public function addManipulation(string $name, array $parameters = []): self
    {
        $this->manipulations[$name] = $parameters;

        return $this;
    }

    public function getManipulationArgument(string $manipulationName): null|string|array
    {
        return $this->manipulations[$manipulationName] ?? null;
    }

    public function getFirstManipulationArgument(string $manipulationName): null|string|int
    {
        $manipulationArgument = $this->getManipulationArgument($manipulationName);

        if (! is_array($manipulationArgument)) {
            return null;
        }

        return $manipulationArgument[0];
    }

    public function isEmpty(): bool
    {
        return count($this->manipulations) === 0;
    }

    public function apply(ImageDriver $image): void
    {
        foreach ($this->manipulations as $manipulationName => $parameters) {
            match ($manipulationName) {
                'border' => (isset($parameters['type'])) && $parameters['type'] = BorderType::from($parameters['type']),
                'watermark' => (isset($parameters['fit'])) && $parameters['fit'] = Fit::from($parameters['fit']),
                'watermark','resizeCanvas','insert' => (isset($parameters['position'])) && $parameters['position'] = AlignPosition::from($parameters['position']),
                'pickColor' => (isset($parameters['colorFormat'])) && $parameters['colorFormat'] = ColorFormat::from($parameters['colorFormat']),
                'resize','width','height' => (isset($parameters['constraints'])) && $parameters['constraints'] = Constraint::from($parameters['constraints']),
                'crop' => (isset($parameters['position'])) && $parameters['position'] = CropPosition::from($parameters['position']),
                'fit' => (isset($parameters['fit'])) && $parameters['fit'] = Fit::from($parameters['fit']),
                'flip' => (isset($parameters['flip'])) && $parameters['flip'] = FlipDirection::from($parameters['flip']),
                default => ''
            };
            $image->$manipulationName(...$parameters);
        }
    }

    public function mergeManipulations(self $manipulations): self
    {
        foreach ($manipulations->toArray() as $name => $parameters) {
            $this->manipulations[$name] = array_merge($this->manipulations[$name] ?? [], $parameters ?: []);
        }

        return $this;
    }

    public function removeManipulation(string $name): self
    {
        unset($this->manipulations[$name]);

        return $this;
    }

    public function toArray(): array
    {
        return $this->manipulations;
    }
}
