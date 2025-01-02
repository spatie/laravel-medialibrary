<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait IsSorted
{
    public function setHighestOrderNumber(): void
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) static::where('model_type', $this->model_type)
            ->where('model_id', $this->model_id)
            ->max($this->determineOrderColumnName());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->determineOrderColumnName());
    }

    /*
     * This function reorders the records: the record with the first id in the array
     * will get the starting order (defaults to 1), the record with the second id
     * will get the starting order + 1, and so on.
     *
     * A starting order number can be optionally supplied.
     */
    public static function setNewOrder(array $ids, int $startOrder = 1): void
    {
        foreach ($ids as $id) {
            $model = static::find($id);
            if (! $model) {
                continue;
            }

            $orderColumnName = $model->determineOrderColumnName();

            $model->$orderColumnName = $startOrder++;

            $model->save();
        }
    }

    protected function determineOrderColumnName(): string
    {
        return $this->sortable['order_column_name'] ?? 'order_column';
    }

    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? true;
    }
}
