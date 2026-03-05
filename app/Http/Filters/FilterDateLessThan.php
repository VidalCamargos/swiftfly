<?php

namespace App\Http\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\Filters\Filter;

class FilterDateLessThan  implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->whereDate($property, '<=', Carbon::parse(Arr::wrap($value)[0]));
    }
}
