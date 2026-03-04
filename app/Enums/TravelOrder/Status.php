<?php

namespace App\Enums\TravelOrder;

enum Status: string
{
    case APPROVED = 'approved';

    case PENDING = 'pending';

    case CANCELED = 'canceled';
}
