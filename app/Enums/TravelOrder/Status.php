<?php

namespace App\Enums\TravelOrder;

use ArchTech\Enums\Values;

enum Status: string
{
    use Values;

    case APPROVED = 'approved';

    case CANCELED = 'canceled';

    case PENDING = 'pending';

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public static function possibleUpdateStatus(): array
    {
        return [self::APPROVED->value, self::CANCELED->value];
    }
}
