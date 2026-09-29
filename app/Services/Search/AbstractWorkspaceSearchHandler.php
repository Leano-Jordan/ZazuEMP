<?php

namespace App\Services\Search;

use Illuminate\Database\Eloquent\Builder;

abstract class AbstractWorkspaceSearchHandler
{
    protected function applyStatusAndDate(Builder $query, string $status, ?string $from, ?string $to, string $dateColumn, bool $hasStatus = true): void
    {
        if ($hasStatus && $status !== '') {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate($dateColumn, '>=', $from);
        }

        if ($to) {
            $query->whereDate($dateColumn, '<=', $to);
        }
    }

    protected function text(Builder $query, string $term, array $columns): void
    {
        if ($term === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($term, $columns) {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', '%'.$term.'%');
            }
        });
    }
}
