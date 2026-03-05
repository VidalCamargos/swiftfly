<?php

namespace App\Http\Queries\TravelOrder;

use App\Http\Filters\FilterDateGreaterThan;
use App\Http\Filters\FilterDateLessThan;
use App\Models\TravelOrder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TravelOrderQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(TravelOrder::whereBelongsTo(auth()->user()));

        $this->allowedFilters([
            AllowedFilter::exact('status'),
            AllowedFilter::callback(
                'destination',
                fn ($query, $value) => $query->where('destination', 'LIKE', "%{$value}%")
            ),
            AllowedFilter::custom('departure_start_date', new FilterDateGreaterThan(), 'departure_date'),
            AllowedFilter::custom('departure_end_date', new FilterDateLessThan(), 'departure_date'),
            AllowedFilter::custom('return_start_date', new FilterDateGreaterThan(), 'return_date'),
            AllowedFilter::custom('return_end_date', new FilterDateLessThan(), 'return_date'),
        ]);

        $this->allowedSorts(['departure_date']);

        $this->defaultSort('-id');
    }
}
