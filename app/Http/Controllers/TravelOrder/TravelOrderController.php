<?php

namespace App\Http\Controllers\TravelOrder;

use App\Http\Controllers\Controller;
use App\Models\TravelOrder;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TravelOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly Factory $auth)
    {

    }

    public function index()
    {
        $this->authorize('viewAny', TravelOrder::class);
    }

    public function show(Request $request, TravelOrder $travelOrder)
    {
        $this->authorize('view', [TravelOrder::class, $travelOrder]);
    }

    public function store()
    {
        $this->authorize('create', TravelOrder::class);

    }

    public function update(Request $request, TravelOrder $travelOrder)
    {
        $this->authorize('viewAny', [TravelOrder::class, $travelOrder]);

    }
}
