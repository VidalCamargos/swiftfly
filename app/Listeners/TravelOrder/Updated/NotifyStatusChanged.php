<?php

namespace App\Listeners\TravelOrder\Updated;

use App\Enums\TravelOrder\Status;
use App\Events\TravelOrder\Updated;
use App\Notifications\TravelOrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyStatusChanged implements ShouldQueue
{
    public function shouldQueue(Updated $event): bool
    {
        $travelOrder = $event->travelOrder;

        return $travelOrder->wasChanged('status')
            && in_array($travelOrder->status, [Status::APPROVED, Status::CANCELED]);
    }

    public function handle(Updated $event): void
    {
        $travelOrder = $event->travelOrder;
        $travelOrderUser = $travelOrder->user;

        $travelOrderUser->notify(new TravelOrderStatusChangedNotification($travelOrder));
    }
}
