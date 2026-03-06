<?php

namespace App\Events\TravelOrder;

use App\Models\TravelOrder;

class Updated
{
    public function __construct(public TravelOrder $travelOrder)
    {
    }
}
