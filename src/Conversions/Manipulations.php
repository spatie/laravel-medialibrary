<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Image;

/** @mixin \Spatie\Image\Drivers\ImageDriver */
class Manipulations
{
    protected array $manipulations = [];

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

    public function isEmpty(): bool
    {
        return count($this->manipulations) === 0;
    }

    public function apply(ImageDriver $image): void
    {
        foreach($this->manipulations as $manipulationName => $parameters) {
            $image->$manipulationName(...$parameters);
        }
    }
}
