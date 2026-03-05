<?php

namespace Tests\Unit\Policies;

use App\Enums\TravelOrder\Status;
use App\Models\TravelOrder;
use App\Models\User;
use App\Policies\TravelOrderPolicy;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TravelOrderPolicyTest extends TestCase
{
    #[DataProvider('policyDataProvider')]
    public function test_policy(string $method, bool $isAdmin, bool $isOwner, bool $expected): void
    {
        $policy = new TravelOrderPolicy();

        $user = User::factory()->create(['is_admin' => $isAdmin]);
        $owner = $isOwner ? $user : User::factory()->create();

        $travelOrder = TravelOrder::factory()->for($owner)->create(['status' => Status::REQUESTED]);

        $actual = match ($method) {
            'viewAny' => $policy->viewAny($user),
            'create' => $policy->create($user),
            'view' => $policy->view($user, $travelOrder),
            'update' => $policy->update($user, $travelOrder),

            default => null
        };

        $this->assertSame($expected, $actual);
    }

    public static function policyDataProvider(): Iterator
    {
        yield 'viewAny always allowed' => ['viewAny', false, true, true];
        yield 'create always allowed' => ['create', false, true, true];

        yield 'view allowed when owner' => ['view', false, true, true];
        yield 'view forbidden when not owner' => ['view', false, false, false];

        yield 'update allowed when admin (owner irrelevant)' => ['update', true, false, true];
        yield 'update forbidden when not admin (owner irrelevant)' => ['update', false, true, false];
    }
}
