<?php

namespace App\DataTables;

use Yajra\DataTables\EloquentDataTable;

class PurchaseDataTable extends EloquentDataTable
{
    public function prepareCountQuery()
    {
        // The purchase query has no grouping or HAVING filters. Count its rows
        // without evaluating invoice, payment, shipping and investor lookups.
        // Keep joins and WHERE conditions for permissions and search filters.
        return (clone $this->query)
            ->reorder()
            ->select('transactions.id')
            ->setBindings([], 'select');
    }
}
