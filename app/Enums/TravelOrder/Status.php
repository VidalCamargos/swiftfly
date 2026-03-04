<?php

namespace App\Enums\TravelOrder;

use ArchTech\Enums\Values;

enum Status: string
{
    use Values;

    case APPROVED = 'approved';

    case PENDING = 'pending';

    case CANCELED = 'canceled';
}
