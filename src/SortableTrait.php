<?php

namespace Spatie\MediaLibrary;

use Spatie\MediaLibrary\Exceptions\InvalidNewOrder;

trait SortableTrait
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber()
    {
        $orderColumnName = $this->determineOrderColumnName();
        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    /**
     * Determine the order value for the new record.
     *
     * @return int
     */
    public function getHighestOrderNumber()
    {
        return (int) static::max($this->determineOrderColumnName());
    }

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->orderBy($this->determineOrderColumnName());
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     *
     * A starting order number can be optionally supplied (defaults to 1).
     *
     * @param array $ids
     * @param int   $startOrder
     *
     * @throws InvalidNewOrder
     */
    public static function setNewOrder($ids, $startOrder = 1)
    {
        if (!is_array($ids)) {
            throw new InvalidNewOrder('You must pass an array to setNewOrder');
        }
        foreach ($ids as $id) {
            $model = static::find($id);
            $orderColumnName = $model->determineOrderColumnName();
            $model->$orderColumnName = $startOrder++;
            $model->save();
        }
    }

    /**
     * Determine the column name of the order column.
     *
     * @return string
     */
    protected function determineOrderColumnName()
    {
        if (
            isset($this->sortable['order_column_name']) &&
            !empty($this->sortable['order_column_name'])
        ) {
            return $this->sortable['order_column_name'];
        }

        return 'order_column';
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     *
     * @return bool
     */
    public function shouldSortWhenCreating()
    {
        if (!isset($this->sortable)) {
            return true;
        }
        if (!isset($this->sortable['sort_when_creating'])) {
            return true;
        }

        return $this->sortable['sort_when_creating'];
    }
}
