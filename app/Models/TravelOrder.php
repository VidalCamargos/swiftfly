<?php

namespace App\Models;

use App\Enums\TravelOrder\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    use HasFactory;

    protected $table = 'travel_orders';

    protected $fillable = [
        'user_id',
        'requester_name',
        'status',
        'destination',
        'departure_date',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'datetime',
            'return_date' => 'datetime',
            'status' => Status::class,
        ];
    }
}
