<?php

namespace App\Models;

use App\Enums\TravelOrder\Status;
use App\Events\TravelOrder\Creating;
use App\Events\TravelOrder\Updated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $dispatchesEvents = [
        'creating' => Creating::class,
        'updated' => Updated::class,
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'datetime',
            'return_date' => 'datetime',
            'status' => Status::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
