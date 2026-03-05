<?php

namespace App\Events\TravelOrder;

use App\Models\TravelOrder;

class Creating
{
    public function __construct(public TravelOrder $travelOrder)
    {
    }
}
