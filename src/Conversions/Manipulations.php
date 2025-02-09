<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\FlipDirection;
use Spatie\Image\Enums\Orientation;

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

    /**
     * @return $this
     */
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
            $parameters = $this->transformParameters($manipulationName, $parameters);
            $image->$manipulationName(...$parameters);
        }
    }

    /**
     * @return $this
     */
    public function mergeManipulations(self $manipulations): self
    {
        foreach ($manipulations->toArray() as $name => $parameters) {
            $this->manipulations[$name] = array_merge($this->manipulations[$name] ?? [], $parameters ?: []);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeManipulation(string $name): self
    {
        unset($this->manipulations[$name]);

        return $this;
    }

    public function toArray(): array
    {
        return $this->manipulations;
    }

    public function transformParameters(int|string $manipulationName, mixed $parameters): mixed
    {
        switch ($manipulationName) {
            case 'border':
                if (isset($parameters['type']) && ! $parameters['type'] instanceof BorderType) {
                    $parameters['type'] = BorderType::from($parameters['type']);
                }
                break;
            case 'watermark':
                if (isset($parameters['fit']) && ! $parameters['fit'] instanceof Fit) {
                    $parameters['fit'] = Fit::from($parameters['fit']);
                }
                // Fallthrough intended for position
            case 'resizeCanvas':
            case 'insert':
                if (isset($parameters['position']) && ! $parameters['position'] instanceof AlignPosition) {
                    $parameters['position'] = AlignPosition::from($parameters['position']);
                }
                break;
            case 'resize':
            case 'width':
            case 'height':
                if (isset($parameters['constraints']) && is_array($parameters['constraints'])) {
                    foreach ($parameters['constraints'] as &$constraint) {
                        if (! $constraint instanceof Constraint) {
                            $constraint = Constraint::from($constraint);
                        }
                    }
                }
                break;
            case 'crop':
                if (isset($parameters['position']) && ! $parameters['position'] instanceof CropPosition) {
                    $parameters['position'] = CropPosition::from($parameters['position']);
                }
                break;
            case 'fit':
                if (isset($parameters['fit']) && ! $parameters['fit'] instanceof Fit) {
                    $parameters['fit'] = Fit::from($parameters['fit']);
                }
                break;
            case 'flip':
                if (isset($parameters['flip']) && ! $parameters['flip'] instanceof FlipDirection) {
                    $parameters['flip'] = FlipDirection::from($parameters['flip']);
                }
                break;
            case 'orientation':
                if (isset($parameters['orientation']) && ! $parameters['orientation'] instanceof Orientation) {
                    $parameters['orientation'] = Orientation::from($parameters['orientation']);
                }
                break;
            default:
                break;
        }

        return $parameters;
    }
}
