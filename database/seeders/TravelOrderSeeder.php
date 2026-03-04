<?php

namespace Database\Seeders;

use App\Enums\TravelOrder\Status;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelOrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        foreach (Status::values() as $status) {
            TravelOrder::factory()->for($user)->create([
                'requester_name' => $user->name,
                'status' => $status,
            ]);
        }
    }
}
