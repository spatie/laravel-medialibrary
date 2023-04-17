<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
     * will get order 1, the record with the second it will get order 2, ...
     *
     * A starting order number can be optionally supplied.
     */
    public static function setNewOrder(array $ids, int $startOrder = 1): void
    {
        $model = new static();
        $primaryKeyColumn  = $model->getKeyName();

        $model
            ->newModelQuery()
            ->whereIn($primaryKeyColumn, $ids)
            ->update([
                $model->determineOrderColumnName() => DB::raw('case '. collect($ids)
                    ->map(function ($id) use ($primaryKeyColumn, &$startOrder): string {
                        return 'when ' . $primaryKeyColumn . ' = ' . DB::getPdo()->quote($id) . ' then ' . ($startOrder++);
                    })
                    ->implode(' ') . ' end')
            ]);
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
