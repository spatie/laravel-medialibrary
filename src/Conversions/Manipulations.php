<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\Image\Drivers\ImageDriver;

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
