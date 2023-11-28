<?php

namespace Spatie\Image;

use ArrayIterator;
use IteratorAggregate;

class OldManipulationSequence implements IteratorAggregate
{
    protected array $groups = [];

    public function __construct(array $sequenceArray = [])
    {
        $this->startNewGroup();
        $this->mergeArray($sequenceArray);
    }

    public function addManipulation(string $operation, string $argument): static
    {
        $lastIndex = count($this->groups) - 1;

        $this->groups[$lastIndex][$operation] = $argument;

        return $this;
    }

    public function merge(self $sequence): static
    {
        $sequenceArray = $sequence->toArray();

        $this->mergeArray($sequenceArray);

        return $this;
    }

    public function mergeArray(array $sequenceArray): void
    {
        foreach ($sequenceArray as $group) {
            foreach ($group as $name => $argument) {
                $this->addManipulation($name, $argument);
            }

            if (next($sequenceArray)) {
                $this->startNewGroup();
            }
        }
    }

    public function startNewGroup(): static
    {
        $this->groups[] = [];

        return $this;
    }

    public function toArray(): array
    {
        return $this->getGroups();
    }

    public function getGroups(): array
    {
        return $this->sanitizeManipulationSets($this->groups);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    public function removeManipulation(string $manipulationName): static
    {
        foreach ($this->groups as &$group) {
            if (array_key_exists($manipulationName, $group)) {
                unset($group[$manipulationName]);
            }
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        if (count($this->groups) > 1) {
            return false;
        }

        if (count($this->groups[0]) > 0) {
            return false;
        }

        return true;
    }

    protected function sanitizeManipulationSets(array $groups): array
    {
        return array_values(array_filter($groups, function (array $manipulationSet) {
            return count($manipulationSet);
        }));
    }

    /*
    * Determine if the sequences contain a manipulation with the given name.
    */
    public function getFirstManipulationArgument($searchManipulationName)
    {
        foreach ($this->groups as $group) {
            foreach ($group as $name => $argument) {
                if ($name === $searchManipulationName) {
                    return $argument;
                }
            }
        }
    }

    /*
    * Determine if the sequences contain a manipulation with the given name.
    */
    public function contains($searchManipulationName): bool
    {
        foreach ($this->groups as $group) {
            foreach ($group as $name => $argument) {
                if ($name === $searchManipulationName) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
