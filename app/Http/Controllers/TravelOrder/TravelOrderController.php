<?php

namespace App\Http\Controllers\TravelOrder;

use App\Http\Controllers\Controller;
use App\Http\Queries\TravelOrder\TravelOrderQuery;
use App\Http\Requests\TravelOrder\StoreRequest;
use App\Http\Requests\TravelOrder\UpdateRequest;
use App\Http\Resources\TravelOrderResource;
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

    public function index(Request $request, TravelOrderQuery $travelOrderQuery)
    {
        $this->authorize('viewAny', TravelOrder::class);

        return TravelOrderResource::collection(
            $travelOrderQuery->simplePaginate($request->input('limit', 10))
                ->appends($request->query()),
        );
    }

    public function show(Request $request, TravelOrder $travelOrder, TravelOrderQuery $travelOrderQuery)
    {
        $this->authorize('view', [TravelOrder::class, $travelOrder]);

        return TravelOrderResource::make(
            $travelOrderQuery->where('id', $travelOrder)->first()
        );
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', TravelOrder::class);

        $travelOrder = $this->auth->user()->travelOrders()->create($request->validated());

        return TravelOrderResource::make($travelOrder);
    }

    public function update(UpdateRequest $request, TravelOrder $travelOrder)
    {
        $this->authorize('viewAny', [TravelOrder::class, $travelOrder]);

       $travelOrder->update($request->validated());

        return TravelOrderResource::make($travelOrder);
    }
}
