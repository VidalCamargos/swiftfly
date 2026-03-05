<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TravelOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TravelOrder $travelOrder): bool
    {
        return $travelOrder->user()->is($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin;
    }
}
