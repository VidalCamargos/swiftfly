<?php

namespace Database\Factories;

use App\Enums\TravelOrder\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;

class TravelOrderFactory extends Factory
{
    use WithFaker;

    public function definition(): array
    {
        return [
            'destination' => $this->faker->word(),
            'departure_date' => now(),
            'requester_name' => $this->faker->name(),
            'status' => Status::APPROVED,
        ];
    }
}
