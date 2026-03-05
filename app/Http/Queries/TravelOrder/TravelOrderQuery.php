<?php

namespace App\Http\Queries\TravelOrder;

use App\Models\TravelOrder;
use Spatie\QueryBuilder\QueryBuilder;

class TravelOrderQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(TravelOrder::whereBelongsTo(auth()->user()));

        $this->defaultSort('id');
    }
}
