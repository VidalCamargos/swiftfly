<?php

namespace App\Listeners\TravelOrder\Creating;

use App\Enums\TravelOrder\Status;
use App\Events\TravelOrder\Creating;

class FillDefaultStatus
{
    public function handle(Creating $event): void
    {
        $event->travelOrder->status = Status::REQUESTED;
    }
}
